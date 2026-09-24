<?php
declare(strict_types=1);

$mode = $calendarMode ?? 'monthly';
$date = $calendarDate ?? date('Y-m-d');
$data = $calendarData ?? [];
$today = date('Y-m-d');
?>
<div class="panel">
    <div class="panel-header">
        <span>Calendar</span>
        <div class="button-row no-print">
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=calendar/daily&date=' . urlencode($date)); ?>">Daily</a>
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=calendar/weekly&date=' . urlencode($date)); ?>">Weekly</a>
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=calendar/monthly&month=' . urlencode(date('Y-m', strtotime($date)))); ?>">Monthly</a>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode($date)); ?>">Open Daily Schedule</a>
        </div>
    </div>
    <div class="panel-body">
        <?php if ($mode === 'daily'): ?>
            <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=calendar/daily'); ?>" class="inline-form no-print">
                <input type="hidden" name="route" value="calendar/daily">
                <input type="date" name="date" value="<?= e($date); ?>">
                <button type="submit" class="btn btn-secondary">View</button>
            </form>
            <?php if (empty($data['day'])): ?>
                <p>No duties scheduled for this day.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Duty</th>
                            <th>Personnel</th>
                            <th>Rank</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['day'] as $entry): ?>
                            <tr>
                                <td><?= e($entry['duty_name'] ?? ''); ?></td>
                                <td><?= e(trim(($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? ''))); ?></td>
                                <td><?= e($entry['rank_abbr'] ?? ''); ?></td>
                                <td><?= e($entry['status'] ?? 'planned'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php elseif ($mode === 'weekly'): ?>
            <div class="calendar-grid weekly-grid">
                <?php foreach ($data['days'] ?? [] as $day): ?>
                    <div class="calendar-day-block">
                        <strong><?= e($day['label']); ?> - <?= e((string) $day['date']); ?></strong>
                        <?php if (empty($day['entries'])): ?>
                            <div class="calendar-empty">No duties</div>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($day['entries'] as $entry): ?>
                                    <li><?= e($entry['duty_name'] ?? ''); ?>: <?= e(trim(($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? ''))); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="calendar-topbar no-print">
                <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=calendar/monthly'); ?>" class="inline-form">
                    <input type="hidden" name="route" value="calendar/monthly">
                    <input type="month" name="month" value="<?= e((string) ($data['year'] ?? date('Y')) . '-' . str_pad((string) ($data['month'] ?? date('m')), 2, '0', STR_PAD_LEFT)); ?>">
                    <button type="submit" class="btn btn-secondary">Go</button>
                </form>
            </div>
            <div class="calendar-grid month-grid">
                <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $label): ?>
                    <div class="calendar-heading"><?= e($label); ?></div>
                <?php endforeach; ?>
                <?php foreach ($data['cells'] ?? [] as $cell): ?>
                    <?php if (!empty($cell['empty'])): ?>
                        <div class="calendar-empty-cell"></div>
                    <?php else: ?>
                        <div class="calendar-day-cell">
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode((string) $cell['date'])); ?>"><?= e((string) date('d', strtotime((string) $cell['date']))); ?></a>
                            <div class="mini-count"><?= e((string) count($cell['entries'] ?? [])); ?> duties</div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
