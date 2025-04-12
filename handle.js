document.addEventListener('DOMContentLoaded', function() {
// ====================== Login Form Validation ======================
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.setAttribute('novalidate', true);
        
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            let valid = true;
            
            // Clear previous error messages
            clearErrorMessages();
            
            // Validate username
            if (!username.value.trim()) {
                showFieldAlert(username, 'Please enter username');
                username.focus();
                valid = false;
            } 
            // Validate password (only if username is valid)
            else if (!password.value.trim()) {
                showFieldAlert(password, 'Please enter password');
                password.focus();
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    }

    function showFieldAlert(inputElement, message) {
        // Remove any existing alert for this field
        const existingAlert = inputElement.parentNode.querySelector('.field-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = 'field-alert';
        alertElement.textContent = message;
        
        // Insert after the input field's parent (to appear below the field)
        inputElement.parentNode.appendChild(alertElement);
        
        // Highlight the input field
        inputElement.classList.add('error-field');
    }

    function clearErrorMessages() {
        document.querySelectorAll('.field-alert').forEach(el => el.remove());
        document.querySelectorAll('.error-field').forEach(el => el.classList.remove('error-field'));
    }

// ====================== Music Search Functionality ======================
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const genreButtons = document.querySelectorAll('.genre-buttons button');
    
    // Remove the hidden search_term input as we won't need it
    const hiddenInput = document.getElementById('searchTerm');
    if (hiddenInput) {
        hiddenInput.remove();
    }
    
    // Handle form submission for text search
    searchForm.addEventListener('submit', function(e) {
        if (searchInput.value.trim() === '') {
            e.preventDefault();
            return;
        }
    });
    
    // Handle genre button clicks
    genreButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Create a new URL with the search parameter
            const url = new URL(searchForm.action, window.location.origin);
            const searchValue = this.getAttribute('data-genre');
            
            // Set the search parameter
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            
            // Navigate to the clean URL
            window.location.href = url.toString();
        });
    });

// ====================== Audio Player Functionality ======================
    // Store all active audio elements
    const activePlayers = new Map();
    
    // Handle play button clicks
    document.addEventListener('click', function(e) {
        const playBtn = e.target.closest('.play-btn');
        if (!playBtn) return;
        
        const musicId = playBtn.getAttribute('musid');
        const musicItem = playBtn.closest('.music-item');
        
        if (activePlayers.has(musicId)) {
            // Track exists - toggle play/pause
            const audio = activePlayers.get(musicId);
            if (audio.paused) {
                audio.play()
                    .then(() => updatePlayerState(musicId, musicItem, true))
                    .catch(handlePlaybackError);
            } else {
                audio.pause();
                updatePlayerState(musicId, musicItem, false);
            }
        } else {
            // New track - create audio element
            const audio = new Audio(`file.php?musid=${encodeURIComponent(musicId)}`);
            activePlayers.set(musicId, audio);
            
            // Set up event listeners
            audio.addEventListener('play', () => updatePlayerState(musicId, musicItem, true));
            audio.addEventListener('pause', () => {
                if (!audio.ended) updatePlayerState(musicId, musicItem, false);
            });
            audio.addEventListener('ended', () => {
                resetPlayerState(musicId, musicItem);
                activePlayers.delete(musicId);
            });
            audio.addEventListener('error', () => {
                handlePlaybackError(musicItem);
                resetPlayerState(musicId, musicItem);
                activePlayers.delete(musicId);
            });
            
            // Start playback
            audio.play()
                .then(() => updatePlayerState(musicId, musicItem, true))
                .catch(error => handlePlaybackError(musicItem));
        }
    });
    
    // Update player UI state
    function updatePlayerState(musicId, musicItem, isPlaying) {
        const audio = activePlayers.get(musicId);
        const playBtn = musicItem.querySelector('.play-btn');
        
        if (isPlaying) {
            // Set playing state
            musicItem.classList.add('playing');
            musicItem.classList.remove('paused');
            playBtn.classList.add('playing');
        } else {
            // Set paused state
            musicItem.classList.add('paused');
            musicItem.classList.remove('playing');
            playBtn.classList.remove('playing');
        }
    }
    
    // Reset player UI state
    function resetPlayerState(musicId, musicItem) {
        const playBtn = musicItem.querySelector('.play-btn');
        musicItem.classList.remove('playing', 'paused');
        playBtn.classList.remove('playing');
    }
    
});