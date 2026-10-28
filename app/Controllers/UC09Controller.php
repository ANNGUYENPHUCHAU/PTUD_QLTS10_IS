<?php
declare(strict_types=1);

final class UC09Controller
{
    private UC09Model $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/Core/uc14-config.php';
        $db = connectDatabase($config);
        $this->model = new UC09Model($db);
    }

    public function run(): void
    {
        $schools = $this->model->getHighSchools();
        $years = $this->model->getAdmissionYears();

        $selectedSchool = (string)($_GET['school'] ?? ($schools[0]['ma_truong_thpt'] ?? 'THPT01'));
        $selectedYear = (int)($_GET['year'] ?? ($years[0] ?? 2026));
        $selectedCriterion = (string)($_GET['criterion'] ?? 'all');

        $stats = $this->model->getStatistics($selectedSchool, $selectedYear);
        $candidates = $this->model->getFilteredCandidates($selectedSchool, $selectedYear, $selectedCriterion);

        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require dirname(__DIR__) . '/Views/Partials/sidebar.php';
        require dirname(__DIR__) . '/Views/UC09/index.php';
    }
}
