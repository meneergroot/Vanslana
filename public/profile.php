<?php
require_once '../inc/header.php';

$username = $_GET['username'] ?? '';
if (empty($username)) {
    header('Location: /');
    exit();
}

// Get creator profile
$sql = "SELECT * FROM users WHERE username = :username";
$creator = fetchOne($sql, [':username' => $username]);

if (!$creator) {
    header('Location: /');
    exit();
}

// Get creator's posts
$sql = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC";
$posts = fetchAll($sql, [':user_id' => $creator['id']]);

// Check if current user is subscribed
$isSubscribed = false;
if (isLoggedIn()) {
    $sql = "SELECT id FROM subscriptions WHERE user_id = :user_id AND creator_id = :creator_id";
    $subscription = fetchOne($sql, [
        ':user_id' => $_SESSION['user_id'],
        ':creator_id' => $creator['id']
    ]);
    $isSubscribed = !empty($subscription);
}

// Handle subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
    
    // Redirect to subscription page
    header('Location: /subscribe.php?creator=' . urlencode($creator['username']));
    exit();
}
?>

<div class="profile-banner" style="background-image: url('<?php echo htmlspecialchars($creator['banner_image'] ?? '/images/default-banner.jpg'); ?>')">
    <img src="<?php echo htmlspecialchars($creator['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
         class="profile-picture" alt="<?php echo htmlspecialchars($creator['username']); ?>">
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($creator['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($creator['description'] ?? 'No description available.'); ?></p>
                    
                    <?php if ($creator['is_creator']): ?>
                        <div class="mb-3">
                            <strong>Subscription Price:</strong> <?php echo htmlspecialchars($creator['subscription_price'] ?? '0'); ?> SOL/month
                        </div>
                        
                        <?php if (isLoggedIn() && $_SESSION['user_id'] !== $creator['id']): ?>
                            <?php if ($isSubscribed): ?>
                                <button class="btn btn-success w-100" disabled>Subscribed</button>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <button type="submit" name="subscribe" class="btn btn-primary w-100">Subscribe</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="post-grid">
                <?php foreach ($posts as $post): ?>
                    <?php if (!$post['is_premium'] || $isSubscribed || $_SESSION['user_id'] === $creator['id']): ?>
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
                    <?php else: ?>
                        <div class="post-card">
                            <div class="premium-badge">Premium Content</div>
                            <div class="post-media" style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                <div class="text-center p-4">
                                    <h5>Premium Content</h5>
                                    <p>Subscribe to view this content</p>
                                    <form method="POST" action="">
                                        <button type="submit" name="subscribe" class="btn btn-primary">Subscribe</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
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