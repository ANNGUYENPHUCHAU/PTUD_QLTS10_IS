<?php
declare(strict_types=1);

session_start();
require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/uc14-helpers.php';
require_once dirname(__DIR__) . '/app/Models/UC14Model.php';
require_once dirname(__DIR__) . '/app/Services/UC14Service.php';
require_once dirname(__DIR__) . '/app/Controllers/UC14Controller.php';

(new UC14Controller())->run();
