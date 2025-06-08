<?php
require_once '../inc/header.php';
requireLogin();

$username = $_GET['creator'] ?? '';
if (empty($username)) {
    header('Location: /');
    exit();
}

// Get creator profile
$sql = "SELECT * FROM users WHERE username = :username AND is_creator = 1";
$creator = fetchOne($sql, [':username' => $username]);

if (!$creator) {
    header('Location: /');
    exit();
}

// Check if already subscribed
$sql = "SELECT id FROM subscriptions WHERE user_id = :user_id AND creator_id = :creator_id";
$subscription = fetchOne($sql, [
    ':user_id' => $_SESSION['user_id'],
    ':creator_id' => $creator['id']
]);

if ($subscription) {
    header('Location: /profile.php?username=' . urlencode($creator['username']));
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Subscribe to <?php echo htmlspecialchars($creator['username']); ?></h2>
                
                <div class="text-center mb-4">
                    <img src="<?php echo htmlspecialchars($creator['profile_picture'] ?? '/images/default-avatar.png'); ?>" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                    <h4><?php echo htmlspecialchars($creator['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($creator['description'] ?? ''); ?></p>
                </div>
                
                <div class="alert alert-info">
                    <h5>Subscription Details</h5>
                    <p class="mb-0">
                        Price: <strong><?php echo htmlspecialchars($creator['subscription_price'] ?? '0'); ?> SOL</strong> per month
                    </p>
                </div>
                
                <div class="text-center mb-4">
                    <button class="wallet-connect mb-3">Connect Phantom Wallet</button>
                    <button class="btn btn-primary subscribe-button" 
                            data-wallet="<?php echo htmlspecialchars($creator['wallet_address']); ?>"
                            data-amount="<?php echo htmlspecialchars($creator['subscription_price'] ?? '0'); ?>"
                            disabled>
                        Subscribe Now
                    </button>
                </div>
                
                <div class="text-muted text-center">
                    <small>
                        By subscribing, you agree to pay <?php echo htmlspecialchars($creator['subscription_price'] ?? '0'); ?> SOL 
                        per month to <?php echo htmlspecialchars($creator['username']); ?>.
                        <br>
                        You can cancel your subscription at any time from your dashboard.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const connectButton = document.querySelector('.wallet-connect');
    const subscribeButton = document.querySelector('.subscribe-button');
    
    connectButton.addEventListener('click', async () => {
        const publicKey = await connectWallet();
        if (publicKey) {
            connectButton.textContent = publicKey.slice(0, 4) + '...' + publicKey.slice(-4);
            connectButton.disabled = true;
            subscribeButton.disabled = false;
        }
    });
    
    subscribeButton.addEventListener('click', async () => {
        const creatorWallet = subscribeButton.dataset.wallet;
        const amount = parseFloat(subscribeButton.dataset.amount);
        
        if (await subscribeToCreator(creatorWallet, amount)) {
            window.location.href = '/profile.php?username=<?php echo urlencode($creator['username']); ?>';
        } else {
            alert('Subscription failed. Please try again.');
        }
    });
});
</script>

<?php require_once '../inc/footer.php'; ?> 