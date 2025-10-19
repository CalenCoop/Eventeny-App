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
    
    <div class="form-group">
        <label for="title">Ticket Title *</label>
        <input type="text" name="title" id="title" required>
    </div>
    
    <div class="form-group">
        <label for="location">Location</label>
        <input type="text" name="location" id="location" required>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4"></textarea>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea name="instructions" id="instructions" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" name="price" id="price" step="0.01" min="0" placeholder="0.00" required>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="quantity">Quantity Available *</label>
            <input type="number" name="quantity" id="quantity" min="1" placeholder="1" required>
        </div>
        <div class="form-group">
            <label for="visibility">Ticket Visibility *</label>
            <select name="visibility" id="visibility" required>
                <option value="public" selected>Public</option>
                <option value="private">Private</option>
            </select>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="sale_start">Sale Start Date & Time *</label>
            <input type="datetime-local" name="sale_start" id="sale_start" 
                   value="<?php echo $saleStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="form-group">
            <label for="sale_end">Sale End Date & Time *</label>
            <input type="datetime-local" id="sale_end" name="sale_end" 
                   value="<?php echo $saleEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="event_start">Event Start Date & Time *</label>
            <input type="datetime-local" name="event_start" id="event_start" 
                   value="<?php echo $eventStart->format('Y-m-d\TH:i'); ?>" required>
        </div>
        <div class="form-group">
            <label for="event_end">Event End Date & Time *</label>
            <input type="datetime-local" name="event_end" id="event_end" 
                   value="<?php echo $eventEnd->format('Y-m-d\TH:i'); ?>" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="image">Image URL (optional)</label>
        <input type="url" name="image" id="image" placeholder="https://example.com/image.jpg">
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Ticket</button>
    </div>
</form>