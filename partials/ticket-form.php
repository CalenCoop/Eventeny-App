<?php
date_default_timezone_set('America/Los_Angeles');
$now = new DateTime();
$saleStart = $now;
$saleEnd = (clone $saleStart)->modify('+2 days +5 hours');
$eventStart = (clone $saleEnd)->modify('+1 day');
$eventEnd = (clone $eventStart)->modify('+6 hours');
?>

<form method="POST" action="dashboard.php" class="ticket-form">
    <input type="hidden" name="action" value="create">
    
    <div class="mb-3">
        <label for="title" class="form-label">Ticket Title *</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" id="location" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="4" class="form-control"></textarea>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="instructions" class="form-label">Instructions</label>
            <textarea name="instructions" id="instructions" rows="4" class="form-control"></textarea>
        </div>
        <div class="col-md-6">
            <label for="price" class="form-label">Price *</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="quantity" class="form-label">Quantity Available *</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="1" required>
        </div>
        <div class="col-md-6">
            <label for="visibility" class="form-label">Ticket Visibility *</label>
            <select name="visibility" id="visibility" class="form-select" required>
                <option value="public" selected>Public</option>
                <option value="private">Private</option>
            </select>
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="sale_start" class="form-label">Sale Start Date & Time *</label>
            <input type="datetime-local" name="sale_start" id="sale_start" class="form-control"
                   value="<?php echo $saleStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="sale_end" class="form-label">Sale End Date & Time *</label>
            <input type="datetime-local" id="sale_end" name="sale_end" class="form-control"
                   value="<?php echo $saleEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="event_start" class="form-label">Event Start Date & Time *</label>
            <input type="datetime-local" name="event_start" id="event_start" class="form-control"
                   value="<?php echo $eventStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="event_end" class="form-label">Event End Date & Time *</label>
            <input type="datetime-local" name="event_end" id="event_end" class="form-control"
                   value="<?php echo $eventEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="image" class="form-label">Image URL (optional)</label>
        <input type="url" name="image" id="image" class="form-control" placeholder="https://example.com/image.jpg">
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Ticket</button>
    </div>
</form>