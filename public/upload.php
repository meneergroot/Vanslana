<?php
require_once '../inc/header.php';
requireCreator();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $isPremium = isset($_POST['is_premium']);
    $isNSFW = isset($_POST['is_nsfw']);
    
    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($_FILES['media']['name'])) {
        $errors[] = "Media file is required";
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($_FILES['media']['type'], $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and MP4 files are allowed.";
        }
        
        if ($_FILES['media']['size'] > $maxSize) {
            $errors[] = "File size too large. Maximum size is 50MB.";
        }
    }
    
    if (empty($errors)) {
        $fileExtension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = '../uploads/' . $fileName;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadPath)) {
            $sql = "INSERT INTO posts (user_id, title, media_url, tags, is_premium, is_nsfw, created_at) 
                    VALUES (:user_id, :title, :media_url, :tags, :is_premium, :is_nsfw, NOW())";
            
            try {
                executeQuery($sql, [
                    ':user_id' => $_SESSION['user_id'],
                    ':title' => $title,
                    ':media_url' => '/uploads/' . $fileName,
                    ':tags' => $tags,
                    ':is_premium' => $isPremium,
                    ':is_nsfw' => $isNSFW
                ]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Failed to save post. Please try again.";
                unlink($uploadPath); // Delete uploaded file if database insert fails
            }
        } else {
            $errors[] = "Failed to upload file. Please try again.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Upload Content</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Post uploaded successfully! <a href="/dashboard.php">View your posts</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="media" class="form-label">Media File</label>
                            <input type="file" class="form-control" id="media" name="media" 
                                   accept=".jpg,.jpeg,.png,.mp4" required>
                            <small class="text-muted">Supported formats: JPG, PNG, MP4 (max 50MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags" 
                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>">
                            <small class="text-muted">Example: art, digital, photography</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_premium" name="is_premium"
                                   <?php echo isset($_POST['is_premium']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_premium">Premium Content (Subscribers Only)</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_nsfw" name="is_nsfw"
                                   <?php echo isset($_POST['is_nsfw']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_nsfw">NSFW Content</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Upload</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mediaInput = document.getElementById('media');
    const uploadArea = document.querySelector('.card-body');
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        if (e.dataTransfer.files.length) {
            mediaInput.files = e.dataTransfer.files;
        }
    });
});
</script>

<?php require_once '../inc/footer.php'; ?> 