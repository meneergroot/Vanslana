<?php
require_once '../inc/header.php';
requireLogin();

$user = getCurrentUser();

// Get user's posts
$sql = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC";
$posts = fetchAll($sql, [':user_id' => $_SESSION['user_id']]);

// Get user's subscriptions
$sql = "SELECT u.*, s.created_at as subscribed_at 
        FROM users u 
        JOIN subscriptions s ON u.id = s.creator_id 
        WHERE s.user_id = :user_id 
        ORDER BY s.created_at DESC";
$subscriptions = fetchAll($sql, [':user_id' => $_SESSION['user_id']]);

// Get user's subscribers (if creator)
$subscribers = [];
if ($user['is_creator']) {
    $sql = "SELECT u.*, s.created_at as subscribed_at 
            FROM users u 
            JOIN subscriptions s ON u.id = s.user_id 
            WHERE s.creator_id = :creator_id 
            ORDER BY s.created_at DESC";
    $subscribers = fetchAll($sql, [':creator_id' => $_SESSION['user_id']]);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Profile</h5>
                <div class="text-center mb-3">
                    <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
                         class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                <h4 class="text-center"><?php echo htmlspecialchars($user['username']); ?></h4>
                <p class="text-center text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <?php if ($user['is_creator']): ?>
                    <div class="text-center">
                        <a href="/upload.php" class="btn btn-primary">Upload Content</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($user['is_creator']): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Subscribers (<?php echo count($subscribers); ?>)</h5>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?php echo htmlspecialchars($subscriber['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
                                 class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <a href="/profile.php?username=<?php echo urlencode($subscriber['username']); ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($subscriber['username']); ?>
                                </a>
                                <small class="d-block text-muted">
                                    Subscribed <?php echo date('M j, Y', strtotime($subscriber['subscribed_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Your Posts</h5>
                <div class="post-grid">
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <?php if ($post['is_nsfw']): ?>
                                <div class="nsfw-warning">NSFW Content - Click to reveal</div>
                            <?php endif; ?>
                            
                            <?php if (strpos($post['media_url'], '.mp4') !== false): ?>
                                <video class="post-media" controls>
                                    <source src="<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                                </video>
                            <?php else: ?>
                                <img class="post-media" src="<?php echo htmlspecialchars($post['media_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            
                            <div class="p-3">
                                <h5><?php echo htmlspecialchars($post['title']); ?></h5>
                                <div class="tags">
                                    <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($post['is_premium']): ?>
                                    <span class="badge bg-warning">Premium</span>
                                <?php endif; ?>
                                <small class="text-muted d-block mt-2">
                                    Posted <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($subscriptions)): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Your Subscriptions</h5>
                    <?php foreach ($subscriptions as $subscription): ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo htmlspecialchars($subscription['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
                                 class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <a href="/profile.php?username=<?php echo urlencode($subscription['username']); ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($subscription['username']); ?>
                                </a>
                                <small class="d-block text-muted">
                                    Subscribed <?php echo date('M j, Y', strtotime($subscription['subscribed_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Handle NSFW content reveal
    document.querySelectorAll('.nsfw-warning').forEach(warning => {
        warning.addEventListener('click', function() {
            this.style.display = 'none';
        });
    });
});
</script>

<?php require_once '../inc/footer.php'; ?> 