document.addEventListener('DOMContentLoaded', () => {
    const urlInput = document.getElementById('urlInput');
    const downloadBtn = document.getElementById('downloadBtn');
    const btnText = document.querySelector('.btn-text');
    const loader = document.querySelector('.loader');
    const errorMsg = document.getElementById('errorMsg');
    const resultContainer = document.getElementById('result');

    // Theme Logic
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('.icon');

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            themeIcon.textContent = '☀️';
        } else {
            document.body.removeAttribute('data-theme');
            themeIcon.textContent = '🌙';
        }
    }

    // Init Theme
    const savedTheme = localStorage.getItem('theme');
    const systemPref = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    applyTheme(savedTheme || systemPref);

    // Toggle Handler
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    });

    // Mobile Menu Logic
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.querySelector('.nav-links');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            // Toggle Active Classes
            hamburger.classList.toggle('open');
            // We use a small timeout to allow display:flex to apply before opacity for transition (optional, but CSS animation handles it mostly)
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                setTimeout(() => navLinks.style.display = 'none', 300); // Wait for transition
            } else {
                navLinks.style.display = 'flex';
                // Force reflow
                void navLinks.offsetWidth;
                navLinks.classList.add('active');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target) && navLinks.classList.contains('active')) {
                hamburger.classList.remove('open');
                navLinks.classList.remove('active');
                setTimeout(() => navLinks.style.display = 'none', 300);
            }
        });
    }

    // Paste Button Logic
    const pasteBtn = document.getElementById('pasteBtn');
    if (pasteBtn) {
        pasteBtn.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                urlInput.value = text;
                urlInput.focus(); // Focus input after paste
            } catch (err) {
                console.error('Failed to read clipboard:', err);
                alert('Please allow clipboard access or paste manually.');
            }
        });
    }

    downloadBtn.addEventListener('click', handleDownload);

    // Allow 'Enter' key to trigger download
    urlInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleDownload();
        }
    });

    async function handleDownload() {
        const url = urlInput.value.trim();

        // Basic Validation
        if (!url) {
            showError('Please paste a TikTok URL.');
            return;
        }

        // Reset UI
        showError('');
        resultContainer.style.display = 'none';
        resultContainer.innerHTML = '';
        setLoading(true);

        try {
            const response = await fetch('api/metadata.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ url: url })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.code === 0 && data.data) {
                renderResult(data.data);
            } else {
                showError(data.msg || 'Failed to fetch video details.');
            }

        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please check your connection or try again.');
        } finally {
            setLoading(false);
        }
    }

    function renderResult(data) {
        let { cover, title, play, hdplay, music, author, size, hd_size } = data;
        const apiBase = 'https://tikwm.com';

        let videoUrl = hdplay || play; // Prioritize HD

        if (cover && !cover.startsWith('http')) cover = apiBase + cover;
        if (videoUrl && !videoUrl.startsWith('http')) videoUrl = apiBase + videoUrl;
        if (music && !music.startsWith('http')) music = apiBase + music;
        if (author.avatar && !author.avatar.startsWith('http')) author.avatar = apiBase + author.avatar;

        const sizeLabel = hd_size > 0 ? `(${formatSize(hd_size)})` : (size > 0 ? `(${formatSize(size)})` : '');

        resultContainer.innerHTML = `
            <div class="horizontal-card">
                <!-- Left Side: Video Preview -->
                <div class="card-preview">
                    <img src="${cover}" alt="Video Thumbnail" class="thumbnail">
                    <div class="play-overlay">▶</div>
                </div>

                <!-- Right Side: Content & Actions -->
                <div class="card-content">
                    <!-- Author Header -->
                    <div class="card-header">
                        <img src="${author.avatar || 'assets/default-user.png'}" alt="User" class="avatar" onerror="this.src='https://via.placeholder.com/60'">
                        <div class="author-info">
                            <span class="author-name">${author.nickname || 'TikTok User'}</span>
                            <span class="author-username">@${author.unique_id || 'username'}</span>
                        </div>
                    </div>

                    <!-- Progress Bar (Dynamic) -->
                     <div id="progressContainer" class="progress-container" style="display: none; margin-bottom: 20px;">
                        <div class="progress-bar">
                            <div id="progressFill" class="progress-fill"></div>
                        </div>
                        <p id="progressText" class="progress-text">0%</p>
                    </div>

                    <!-- Video Title -->
                    <p class="video-title">${title ? title : 'No Tagline'}</p>

                    <!-- Action Buttons -->
                    <div class="card-actions">
                        <button id="dlVideoBtn" class="btn-primary">
                            Download Video HD ${sizeLabel}
                        </button>
                        <button id="dlAudioBtn" class="btn-secondary">
                            🎵 Download Audio MP3
                        </button>
                    </div>
                     <button id="resetBtn" class="btn-link">Download Another</button>
                </div>
            </div>
        `;
        resultContainer.style.display = 'block';

        // Attach Events
        document.getElementById('dlVideoBtn').addEventListener('click', () => downloadVideo(videoUrl, title, 'mp4'));
        document.getElementById('dlAudioBtn').addEventListener('click', () => downloadVideo(music, title, 'mp3'));

        // Reset Event
        document.getElementById('resetBtn').addEventListener('click', () => {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
            urlInput.value = '';
            urlInput.focus();
        });
    }

    function formatSize(bytes) {
        if (!bytes) return '';
        const k = 1024;
        const main_sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + main_sizes[i];
    }

    async function downloadVideo(url, filename, ext = 'mp4') {
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        // Reset and show progress
        progressContainer.style.display = 'block';
        progressFill.style.width = '0%';
        progressText.textContent = '0%';

        // Ensure proper encoding for URL parameters
        // encodeURIComponent handles special chars in filenames and URL symbols properly
        const proxyUrl = `api/stream.php?url=${encodeURIComponent(url)}&name=${encodeURIComponent(filename || 'tiktok_media')}`;
        console.log('Fetching Proxy URL:', proxyUrl);

        try {
            const response = await fetch(proxyUrl);

            // 1. IMPROVE ERROR LOGGING
            if (!response.ok) {
                // Try to get error details from the server response
                let errorDetails = response.statusText;
                try {
                    const errorJson = await response.json();
                    if (errorJson && errorJson.error) {
                        errorDetails = errorJson.error;
                    }
                } catch (e) {
                    // response wasn't JSON, ignore
                }

                console.error(`Stream Error: ${response.status} - ${errorDetails}`);
                throw new Error(`Server returned ${response.status}: ${errorDetails}`);
            }

            const contentLength = +response.headers.get('Content-Length');
            const reader = response.body.getReader();

            let receivedLength = 0;
            let chunks = [];

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                chunks.push(value);
                receivedLength += value.length;

                if (contentLength) {
                    const percent = Math.round((receivedLength / contentLength) * 100);
                    progressFill.style.width = `${percent}%`;
                    progressText.textContent = `${percent}%`;
                }
            }

            const blob = new Blob(chunks);
            const downloadUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `${filename || 'media'}.${ext}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(downloadUrl);

            setTimeout(() => {
                progressContainer.style.display = 'none';
            }, 2000);

        } catch (error) {
            console.error('Streaming failed:', error);

            // 3. FALLBACK MECHANISM
            // If the JS streaming fails (e.g., CORS issue, network interrupt, output buffering weirdness), 
            // fallback to direct browser navigation which handles downloads more robustly.
            console.warn('Switching to direct fallback download...');
            showError('Streaming interrupted. Switching to direct download...');

            // Wait a moment for the user to see the message (optional)
            setTimeout(() => {
                // Hide progress bar as we can't track direct download
                progressContainer.style.display = 'none';
                showError(''); // Clear error

                // Trigger direct download
                window.location.href = proxyUrl;
            }, 1000);
        }
    }

    function showError(msg) {
        errorMsg.textContent = msg;
        errorMsg.style.display = msg ? 'block' : 'none';
    }

    function setLoading(isLoading) {
        if (isLoading) {
            btnText.style.display = 'none';
            loader.style.display = 'block';
            downloadBtn.disabled = true;
        } else {
            btnText.style.display = 'inline';
            loader.style.display = 'none';
            downloadBtn.disabled = false;
        }
    }
});
