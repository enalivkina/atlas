<?php

declare(strict_types=1);

namespace Atlas\Resource;

use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Event;
use Atlas\Http\Exceptions\HttpBadRequestException;
use Atlas\Http\Exceptions\HttpForbiddenException;
use Atlas\Http\Exceptions\HttpNotFoundException;
use Atlas\Http\Response\CreateResponse;
use Atlas\Http\Response\DeleteResponse;
use Atlas\Http\Response\JsonResponse;
use Atlas\Http\Response\PatchResponse;
use Atlas\Http\Response\UpdateResponse;
use Atlas\Resource\Contract\ResourceDataFilterInterface;
use Atlas\Resource\Contract\ResourceWriterInterface;
use Atlas\Resource\Enum\ResourceActionType;
use Atlas\Resource\Enum\ResourceEvent;
use Atlas\Resource\FormRequest\Contract\FormRequestFactoryInterface;
use Atlas\Resource\FormRequest\Contract\FormRequestInterface;
use Atlas\Resource\FormRequest\FormRequest;
use InvalidArgumentException;

abstract class AbstractResourceController
{
    public function __construct(
        protected ResourceDataFilterInterface $resourceDataFilter,
        protected ServerRequestInterface $request,
        protected FormRequestFactoryInterface $formRequestFactory,
        protected ResourceWriterInterface $resourceWriter,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFields())
            ->setAccessibleFilters($this->getAccessibleFilters())
            ->setRelationships($this->getRelationships());

        $this->resourceWriter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFields());
    }

    protected function getForms(): array
    {
        return [
            ResourceActionType::CREATE->value => [FormRequest::class, $this->getFieldRules()],
            ResourceActionType::UPDATE->value => [FormRequest::class, $this->getFieldRules()],
            ResourceActionType::PATCH->value => [FormRequest::class, $this->getFieldRules()],
        ];
    }

    protected function getAvailableActions(): array
    {
        return [
            ResourceActionType::INDEX,
            ResourceActionType::VIEW,
            ResourceActionType::CREATE,
            ResourceActionType::UPDATE,
            ResourceActionType::PATCH,
            ResourceActionType::DELETE,
        ];
    }

    protected function getFieldRules(): array
    {
        return [];
    }

    protected function getRelationships(): array
    {
        return [];
    }

    abstract protected function getResourceName(): string;

    /**
     * Возврат имен свойств ресурса, доступных к чтению
     * Пример запроса:
     * ?fields=id,order_id,name
     *
     * @return array
     */
    abstract protected function getAccessibleFields(): array;

    /**
     * Возврат имен свойств ресурса, доступных к фильтрации
     * Пример запроса:
     * ?filter[order_id][$eq]=3
     *
     * @return array
     */
    abstract protected function getAccessibleFilters(): array;

    /**
     * @throws HttpForbiddenException
     */
    private function checkCallAvailability(ResourceActionType $actionType): void
    {
        if (in_array($actionType, $this->getAvailableActions(), true) === false) {
            throw new HttpForbiddenException("Метод {$actionType->value} запрещен");
        }
    }

    /**
     * Возврат ресурсов, по ограничениям указанным в строке запроса
     * Пример запроса:
     * ?fields[]=id&fields[]=order_id&fields[]=name&filter[order_id][$eq]=3
     * Пример ответа:
     * application/json
     * [
     *     {
     *         "id": 1,
     *         "order_id":3,
     *         "name": "Некоторое имя 1"
     *     },
     *     {
     *         "id": 2,
     *         "order_id":3,
     *         "name": "Некоторое имя 2"
     *     },
     *     ...
     * ]
     *
     * @return JsonResponse
     * @throws HttpNotFoundException
     * @throws HttpForbiddenException
     */
    public function actionList(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionType::INDEX);

        $conditions = $this->request->getQueryParams();

        $this->eventDispatcher->trigger(ResourceEvent::LIST_REQUEST->value, new Event([
            'resource' => $this->getResourceName(),
            'filters' => $conditions['filter'] ?? null,
            'fields' => $conditions['fields'] ?? $this->getAccessibleFields(),
        ]));

        $data = $this->resourceDataFilter->filterAll($this->request->getQueryParams());

        return new JsonResponse($data);
    }

    /**
     * Возврат ресурса, по ограничениям указанным в строке запроса
     * Пример запроса:
     * ?fields[]=id&fields[]=name
     * Пример ответа:
     * application/json
     * {
     *     "id": 1,
     *     "name": "Некоторое имя 1"
     * },
     *
     * @param int $id
     * @return JsonResponse
     * @throws HttpForbiddenException
     */
    public function actionView(int $id): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionType::VIEW);

        $conditions = $this->request->getQueryParams();
        $conditions['filter'] = ['id' => ['$eq' => $id]];

        $this->eventDispatcher->trigger(ResourceEvent::VIEW_REQUEST->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
            'fields' => $conditions['fields'] ?? $this->getAccessibleFields(),
        ]));

        $data = $this->resourceDataFilter->filterOne($conditions);

        return new JsonResponse($data);
    }

    /**
     * @throws HttpForbiddenException
     * @throws HttpBadRequestException
     */
    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionType::CREATE);

        $form = $this->buildForm(ResourceActionType::CREATE->value);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException($form->getErrors());
        }

        $this->eventDispatcher->trigger(ResourceEvent::BEFORE_CREATE->value, new Event([
            'resource' => $this->getResourceName(),
            'values' => $form->getValues(),
        ]));

        try {
            $createdId = $this->resourceWriter->create($form->getValues());
        } catch (InvalidArgumentException $exception) {
            throw new HttpBadRequestException($exception->getMessage());
        }

        $this->eventDispatcher->trigger(ResourceEvent::CREATED->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $createdId,
            'values' => $form->getValues(),
        ]));

        return new CreateResponse($createdId);
    }

    /**
     * @throws HttpForbiddenException
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     */
    public function actionUpdate(int $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionType::UPDATE);

        $form = $this->buildForm(ResourceActionType::UPDATE->value);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException($form->getErrors());
        }

        $this->eventDispatcher->trigger(ResourceEvent::BEFORE_UPDATE->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
            'values' => $form->getValues(),
        ]));

        try {
            $rowsCount = $this->resourceWriter->update($id, $form->getValues());
        } catch (InvalidArgumentException $exception) {
            throw new HttpBadRequestException($exception->getMessage());
        }

        if ($rowsCount === 0) {
            throw new HttpNotFoundException();
        }

        $this->eventDispatcher->trigger(ResourceEvent::UPDATED->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
            'values' => $form->getValues(),
        ]));

        return new UpdateResponse();
    }

    /**
     * @throws HttpForbiddenException
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     */
    public function actionPatch(int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionType::PATCH);

        $form = $this->buildForm(ResourceActionType::PATCH->value);

        $form->setSkipEmptyValues();

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException($form->getErrors());
        }

        $this->eventDispatcher->trigger(ResourceEvent::BEFORE_UPDATE->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
            'values' => $form->getValues(),
        ]));

        try {
            $rowsCount = $this->resourceWriter->patch($id, $form->getValues());
        } catch (InvalidArgumentException $exception) {
            throw new HttpBadRequestException($exception->getMessage());
        }

        if ($rowsCount === 0) {
            throw new HttpNotFoundException();
        }

        $this->eventDispatcher->trigger(ResourceEvent::UPDATED->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
            'values' => $form->getValues(),
        ]));

        return new PatchResponse();
    }

    /**
     * @throws HttpForbiddenException
     * @throws HttpNotFoundException
     */
    public function actionDelete(int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionType::DELETE);

        $this->eventDispatcher->trigger(ResourceEvent::BEFORE_DELETE->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
        ]));

        $rowsCount = $this->resourceWriter->delete($id);

        if ($rowsCount === 0) {
            throw new HttpNotFoundException();
        }

        $this->eventDispatcher->trigger(ResourceEvent::DELETED->value, new Event([
            'resource' => $this->getResourceName(),
            'id' => $id,
        ]));

        return new DeleteResponse();
    }

    private function buildForm(string $action): FormRequestInterface
    {
        $formParams = $this->getForms()[$action] ?? null;

        if (is_array($formParams) === true && count($formParams) === 2) {
            return $this->formRequestFactory->create($formParams[0], $formParams[1]);
        }

        if (is_string($formParams) === true) {
            return $this->formRequestFactory->create($formParams);
        }

        throw new InvalidArgumentException('Форма должна быть задана либо строкой имя класса либо массивом [имя класса, набор правил]');
    }
}
