<?php
require_once '../inc/header.php';

// Get latest posts
$sql = "SELECT p.*, u.username, u.profile_picture 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.is_premium = 0 
        ORDER BY p.created_at DESC 
        LIMIT 20";
$posts = fetchAll($sql);
?>

<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4">Latest Posts</h1>
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
                        <img class="post-media" src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="p-3">
                        <h5><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="text-muted">
                            By <a href="/profile.php?username=<?php echo urlencode($post['username']); ?>">
                                <?php echo htmlspecialchars($post['username']); ?>
                            </a>
                        </p>
                        <div class="tags">
                            <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Popular Creators</h5>
                <?php
                $sql = "SELECT u.*, COUNT(s.id) as subscriber_count 
                        FROM users u 
                        LEFT JOIN subscriptions s ON u.id = s.creator_id 
                        WHERE u.is_creator = 1 
                        GROUP BY u.id 
                        ORDER BY subscriber_count DESC 
                        LIMIT 5";
                $creators = fetchAll($sql);
                
                foreach ($creators as $creator):
                ?>
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo htmlspecialchars($creator['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
                             class="rounded-circle me-2" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <a href="/profile.php?username=<?php echo urlencode($creator['username']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($creator['username']); ?>
                            </a>
                            <small class="d-block text-muted"><?php echo $creator['subscriber_count']; ?> subscribers</small>
                        </div>
                    </div>
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