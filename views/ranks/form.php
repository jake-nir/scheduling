<?php
declare(strict_types=1);

$rankData = $rank ?? ['id' => 0, 'rank_name' => '', 'rank_abbr' => '', 'rank_order' => 0, 'is_active' => 1];
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=ranks/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?= e((string) ($rankData['id'] ?? 0)); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Rank name</label>
                    <input type="text" name="rank_name" value="<?= e($rankData['rank_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Abbreviation</label>
                    <input type="text" name="rank_abbr" value="<?= e($rankData['rank_abbr'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Order</label>
                    <input type="number" name="rank_order" value="<?= e((string) ($rankData['rank_order'] ?? 0)); ?>">
                </div>

                <div class="form-group">
                    <label>Active</label>
                    <input type="checkbox" name="is_active" value="1" <?= ((int) ($rankData['is_active'] ?? 1) === 1) ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=ranks/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>
