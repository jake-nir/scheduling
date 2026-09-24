<?php
declare(strict_types=1);

$personData = $person ?? ['id' => 0, 'service_number' => '', 'first_name' => '', 'middle_name' => '', 'last_name' => '', 'suffix' => '', 'designation' => '', 'unit' => '', 'contact_number' => '', 'email' => '', 'status' => 'active', 'rank_id' => 0];
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=personnel/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?= e((string) ($personData['id'] ?? 0)); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Service number</label>
                    <input type="text" name="service_number" value="<?= e($personData['service_number'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Rank</label>
                    <select name="rank_id" required>
                        <?php foreach ($ranks as $rank): ?>
                            <option value="<?= e((string) $rank['id']); ?>" <?= ((int) ($personData['rank_id'] ?? 0) === (int) $rank['id']) ? 'selected' : ''; ?>>
                                <?= e($rank['rank_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>First name</label>
                    <input type="text" name="first_name" value="<?= e($personData['first_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Middle name</label>
                    <input type="text" name="middle_name" value="<?= e($personData['middle_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Last name</label>
                    <input type="text" name="last_name" value="<?= e($personData['last_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Suffix</label>
                    <input type="text" name="suffix" value="<?= e($personData['suffix'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" name="designation" value="<?= e($personData['designation'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" value="<?= e($personData['unit'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Contact number</label>
                    <input type="text" name="contact_number" value="<?= e($personData['contact_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($personData['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= (($personData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= (($personData['status'] ?? 'active') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=personnel/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>
