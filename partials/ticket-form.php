<?php
date_default_timezone_set('UTC');
$now = new DateTime();
$saleStart = $now;
$saleEnd = (clone $saleStart)->modify('+2 days +5 hours');
$eventStart = (clone $saleEnd)->modify('+1 day');
$eventEnd = (clone $eventStart)->modify('+6 hours');

$isEditing = !empty($edit);

function dt_val($raw, $fallback){
    if(!$raw) return $fallback;
    $dt = new DateTime($raw, new DateTimeZone('UTC')); 
    return $dt->format('Y-m-d\TH:i');
}
?>

<form method="POST" action="dashboard.php" class="ticket-form" id= "ticketForm">
    <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create'; ?>">
    <?php if($isEditing): ?> 
        <input type="hidden" name = "id" value ="<?= (int)$edit['id'];?> ">
    <?php endif ?> 
    
    <div class="mb-3">
        <label for="title" class="form-label">Ticket Title *</label>
        <input type="text" name="title" id="title" class="form-control" required
        value = "<?= htmlspecialchars($isEditing ? ($edit['title'] ?? '' ): ''); ?>">
    </div>
    
    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" id="location" class="form-control" required
        value = "<?= htmlspecialchars($isEditing ? ($edit['location'] ?? '' ): ''); ?>">
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="4" class="form-control"><?= htmlspecialchars($isEditing ? ($edit['description'] ?? '') : '');?></textarea>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="instructions" class="form-label">Instructions</label>
            <textarea name="instructions" id="instructions" rows="4" class="form-control"><?= htmlspecialchars($isEditing ? ($edit['instructions'] ?? '') : '');?></textarea>
        </div>
        <div class="col-md-6">
            <label for="price" class="form-label">Price *</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" placeholder="0.00" required  
            value = "<?= htmlspecialchars($isEditing ? ($edit['price'] ?? '' ): ''); ?>" >
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="quantity" class="form-label">Quantity Available *</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="1" required
            value = "<?= htmlspecialchars($isEditing ? ($edit['quantity'] ?? '' ): ''); ?>" >
        </div>
        <div class="col-md-6">
            <label for="visibility" class="form-label">Ticket Visibility *</label>
            <select name="visibility" id="visibility" class="form-select" required>
                <option value="public" <?= ($isEditing && $edit['visibility']==='public') ? 'selected' : ''; ?>>Public</option>
                <option value="private" <?= ($isEditing && $edit['visibility']==='private') ? 'selected' : ''; ?>>Private</option>
            </select>
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="sale_start" class="form-label">Sale Start Date & Time *</label>
            <input type="datetime-local" name="sale_start" id="sale_start" class="form-control"
            value="<?= $isEditing ? dt_val($edit['sale_start'], $saleStart->format('Y-m-d\TH:i')) : $saleStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="sale_end" class="form-label">Sale End Date & Time *</label>
            <input type="datetime-local" id="sale_end" name="sale_end" class="form-control"
            value="<?= $isEditing ? dt_val($edit['sale_end'], $saleEnd->format('Y-m-d\TH:i')) : $saleEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="event_start" class="form-label">Event Start Date & Time *</label>
            <input type="datetime-local" name="event_start" id="event_start" class="form-control"
            value="<?= $isEditing ? dt_val($edit['event_start'], $eventStart->format('Y-m-d\TH:i')) : $eventStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="event_end" class="form-label">Event End Date & Time *</label>
            <input type="datetime-local" name="event_end" id="event_end" class="form-control"
            value="<?= $isEditing ? dt_val($edit['event_end'], $eventEnd->format('Y-m-d\TH:i')) : $eventEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="image" class="form-label">Image URL (optional)</label>
        <input type="url" name="image" id="image" class="form-control" placeholder="https://example.com/image.jpg"
            value = "<?= htmlspecialchars($isEditing ? ($edit['image'] ?? '' ): ''); ?>" >
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $isEditing ? "Update Ticket" : "Create Ticket";?></button>
        <?php if($isEditing): ?> 
            <a href="dashboard.php" class="btn btn-secondary">Cancel </a>
        <?php endif; ?>
    </div>
</form>