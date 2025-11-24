zzzzzx
    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::listAction(request: $request);
        // $viewData = [
        //     'title' => 'Testy Index Action',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_TESTY->view(), $this->buildCommonViewData($viewData));
    }


    /** {@inheritdoc} */
    protected function fetchListRecords(ServerRequestInterface $request, int $limit, int $offset, array $orderBy): array
    {
        $listFields = $this->listType->getFields(); // Get fields from ListType
        $pageEntity = $this->scrap->getPageEntity();
        $routeType  = $this->scrap->getRouteType();

        // Implement Testy-specific logic to fetch records
        if ($routeType === "account") {
            $userId = $this->scrap->getUserId();
            return $this->repository->findByUserIdWithFields(
                $userId,
                $listFields,
                $orderBy,
                $limit,
                $offset
            );
        } elseif ($routeType === "store") {
            $storeId = $this->scrap->getStoreId();
            return $this->repository->findByStoreIdWithFields(
                $storeId,
                $listFields,
                $orderBy,
                $limit,
                $offset
            );
        } else { // 'core' route or other default
            // Assuming for 'user' entity in core, we fetch all, otherwise by store ID
            if ($pageEntity === 'user') { // This logic would typically be in UserController
                return $this->repository->findAllWithFields(
                    $listFields,
                    $orderBy,
                    $limit,
                    $offset
                );
            } else {
                return $this->repository->findAllWithFields(
                    $listFields,
                    $orderBy,
                    $limit,
                    $offset
                );
            }
        }
    }

    /** {@inheritdoc} */
    protected function fetchTotalListRecords(ServerRequestInterface $request): int
    {
        $pageEntity = $this->scrap->getPageEntity();
        $routeType  = $this->scrap->getRouteType();

        // Implement Testy-specific logic to get total count
        if ($routeType === "account") {
            $userId = $this->scrap->getUserId();
            return $this->repository->countByUserId($userId);
        } elseif ($routeType === "store") {
            $storeId = $this->scrap->getStoreId();
            return $this->repository->countByStoreId($storeId);
        } else { // 'core' route or other default
            if ($pageEntity === 'user') { // This logic would typically be in UserController
                return $this->repository->countAll();
            } else {
                return $this->repository->countAll();
            }
        }
    }
