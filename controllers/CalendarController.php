<?php
declare(strict_types=1);

class CalendarController
{
    public function index(): void
    {
        require_permission('calendar.view');
        $this->monthly();
    }

    public function daily(): void
    {
        require_permission('calendar.view');

        $pageTitle = 'Calendar - Daily';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        if (!validate_date($date)) {
            $date = date('Y-m-d');
        }

        $dayEntries = self::scheduleForDate($date);
        $viewFile = APP_ROOT . '/views/scheduling/calendar.php';
        $calendarMode = 'daily';
        $calendarDate = $date;
        $calendarData = ['day' => $dayEntries, 'date' => $date];

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function weekly(): void
    {
        require_permission('calendar.view');

        $pageTitle = 'Calendar - Weekly';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $start = validate_date($date) ? new DateTime($date) : new DateTime();
        $start->modify('monday this week');
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = clone $start;
            $day->modify('+' . $i . ' day');
            $dayKey = $day->format('Y-m-d');
            $days[] = [
                'date' => $dayKey,
                'label' => $day->format('D'),
                'entries' => self::scheduleForDate($dayKey),
            ];
        }

        $viewFile = APP_ROOT . '/views/scheduling/calendar.php';
        $calendarMode = 'weekly';
        $calendarDate = $date;
        $calendarData = ['days' => $days];

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function monthly(): void
    {
        require_permission('calendar.view');

        $pageTitle = 'Calendar - Monthly';
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        $parts = explode('-', $month);
        $year = count($parts) >= 2 ? (int) $parts[0] : (int) date('Y');
        $monthNumber = count($parts) >= 2 ? (int) $parts[1] : (int) date('m');

        $start = new DateTime(sprintf('%04d-%02d-01', $year, $monthNumber));
        $firstDay = (int) $start->format('N');
        $daysInMonth = (int) $start->format('t');
        $cells = [];

        for ($i = 1; $i <= $firstDay - 1; $i++) {
            $cells[] = ['empty' => true, 'date' => null];
        }

        for ($dayNumber = 1; $dayNumber <= $daysInMonth; $dayNumber++) {
            $day = sprintf('%04d-%02d-%02d', $year, $monthNumber, $dayNumber);
            $cells[] = [
                'empty' => false,
                'date' => $day,
                'entries' => self::scheduleForDate($day),
            ];
        }

        $viewFile = APP_ROOT . '/views/scheduling/calendar.php';
        $calendarMode = 'monthly';
        $calendarDate = $month;
        $calendarData = ['cells' => $cells, 'year' => $year, 'month' => $monthNumber];

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function rotation(): void
    {
        require_permission('calendar.view');
        redirect(APP_PUBLIC_URL . '/?route=rotation/index');
    }

    private static function scheduleForDate(string $date): array
    {
        return Schedule::allForDate($date);
    }
}
