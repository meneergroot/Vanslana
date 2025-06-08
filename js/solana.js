// Check if Phantom is installed
const isPhantomInstalled = window.solana && window.solana.isPhantom;

// Initialize connection to Solana network
const connection = new solanaWeb3.Connection(
    solanaWeb3.clusterApiUrl('mainnet-beta'),
    'confirmed'
);

// Connect to Phantom wallet
async function connectWallet() {
    try {
        if (!isPhantomInstalled) {
            window.open('https://phantom.app/', '_blank');
            return null;
        }

        const resp = await window.solana.connect();
        return resp.publicKey.toString();
    } catch (err) {
        console.error('Error connecting to wallet:', err);
        return null;
    }
}

// Subscribe to a creator
async function subscribeToCreator(creatorWallet, subscriptionAmount) {
    try {
        if (!window.solana.isConnected) {
            await connectWallet();
        }

        const transaction = new solanaWeb3.Transaction();
        
        // Create transfer instruction
        const transferInstruction = solanaWeb3.SystemProgram.transfer({
            fromPubkey: window.solana.publicKey,
            toPubkey: new solanaWeb3.PublicKey(creatorWallet),
            lamports: subscriptionAmount * solanaWeb3.LAMPORTS_PER_SOL
        });

        transaction.add(transferInstruction);

        // Sign and send transaction
        const signature = await window.solana.signAndSendTransaction(transaction);
        
        // Wait for confirmation
        await connection.confirmTransaction(signature.signature);

        // Send subscription confirmation to server
        await fetch('/api/subscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                creator_wallet: creatorWallet,
                subscription_amount: subscriptionAmount,
                transaction_signature: signature.signature
            })
        });

        return true;
    } catch (err) {
        console.error('Error subscribing:', err);
        return false;
    }
}

// Handle wallet connection button
document.addEventListener('DOMContentLoaded', () => {
    const connectButton = document.querySelector('.wallet-connect');
    if (connectButton) {
        connectButton.addEventListener('click', async () => {
            const publicKey = await connectWallet();
            if (publicKey) {
                connectButton.textContent = publicKey.slice(0, 4) + '...' + publicKey.slice(-4);
                connectButton.disabled = true;
            }
        });
    }
});

// Handle subscription buttons
document.addEventListener('click', async (e) => {
    if (e.target.matches('.subscribe-button')) {
        const creatorWallet = e.target.dataset.wallet;
        const amount = parseFloat(e.target.dataset.amount);
        
        if (await subscribeToCreator(creatorWallet, amount)) {
            alert('Subscription successful!');
            location.reload();
        } else {
            alert('Subscription failed. Please try again.');
        }
    }
}); 