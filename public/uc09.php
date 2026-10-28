<?php
declare(strict_types=1);

session_start();
require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/uc14-helpers.php';
require_once dirname(__DIR__) . '/app/Models/UC09Model.php';
require_once dirname(__DIR__) . '/app/Controllers/UC09Controller.php';

(new UC09Controller())->run();
