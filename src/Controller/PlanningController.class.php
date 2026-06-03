<?php

namespace Src\Controller;

class PlanningController extends BaseController
{
    public function getIndex(): void
    {
        (new WorkController())->getSelf();
    }
}
