@php
    $avatarUrl = $profile->avatar_path ? Storage::url($profile->avatar_path) : null;
    $sizeClass = ($variant ?? 'header') === 'edit' ? 'mmhc-avatar-upload--edit' : 'mmhc-avatar-upload--header';
@endphp

<div class="mmhc-avatar-upload {{ $sizeClass }}" id="mmhcAvatarUpload" data-upload-url="{{ route('profile.upload-avatar') }}">
    <div class="mmhc-avatar-upload__ring" role="group" aria-label="Profile photo">
        <div class="mmhc-avatar-upload__preview">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Your profile photo" class="mmhc-avatar-upload__img" id="mmhcAvatarImg">
            @else
                <img src="" alt="" class="mmhc-avatar-upload__img d-none" id="mmhcAvatarImg" aria-hidden="true">
                <div class="mmhc-avatar-upload__placeholder" id="mmhcAvatarPlaceholder">
                    <i class="fas fa-user" aria-hidden="true"></i>
                </div>
            @endif
            <div class="mmhc-avatar-upload__loading d-none" id="mmhcAvatarLoading" aria-hidden="true">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
        <label class="mmhc-avatar-upload__camera" for="mmhcAvatarFile" title="Change profile photo">
            <i class="fas fa-camera" aria-hidden="true"></i>
            <span class="visually-hidden">Change profile photo</span>
        </label>
    </div>
    <input type="file"
           id="mmhcAvatarFile"
           class="mmhc-avatar-upload__input"
           accept="image/jpeg,image/png,image/jpg,image/gif"
           capture="user">
    <button type="button" class="mmhc-avatar-upload__btn" id="mmhcAvatarChooseBtn">
        <i class="fas fa-image me-1" aria-hidden="true"></i>
        {{ $avatarUrl ? 'Change photo' : 'Add photo' }}
    </button>
    <p class="mmhc-avatar-upload__hint">JPG, PNG or GIF · max 2 MB</p>
    <p class="mmhc-avatar-upload__status d-none" id="mmhcAvatarStatus" role="status"></p>
</div>

<style>
.mmhc-avatar-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.mmhc-avatar-upload--header {
    margin: 0 auto 16px;
}
.mmhc-avatar-upload--edit {
    margin: 0 auto 20px;
    padding-top: 8px;
}
.mmhc-avatar-upload__ring {
    position: relative;
    width: 100px;
    height: 100px;
}
.mmhc-avatar-upload--edit .mmhc-avatar-upload__ring {
    width: 112px;
    height: 112px;
}
.mmhc-avatar-upload__preview {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #667eea;
    background: #f1f5f9;
    position: relative;
}
.mmhc-avatar-upload__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.mmhc-avatar-upload__placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 2.5rem;
}
.mmhc-avatar-upload__loading {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #667eea;
}
.mmhc-avatar-upload__camera {
    position: absolute;
    right: -2px;
    bottom: -2px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    margin: 0;
}
.mmhc-avatar-upload__camera:hover {
    filter: brightness(1.05);
}
.mmhc-avatar-upload__input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}
.mmhc-avatar-upload__btn {
    margin-top: 12px;
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
}
.mmhc-avatar-upload__btn:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}
.mmhc-avatar-upload__hint {
    margin: 6px 0 0;
    font-size: 0.75rem;
    color: #64748b;
}
.mmhc-avatar-upload__status {
    margin: 8px 0 0;
    font-size: 0.8rem;
    max-width: 260px;
}
.mmhc-avatar-upload__status.is-success {
    color: #047857;
}
.mmhc-avatar-upload__status.is-error {
    color: #b91c1c;
}
</style>

<script>
(function () {
    var root = document.getElementById('mmhcAvatarUpload');
    if (!root || root.dataset.mmhcAvatarBound === '1') {
        return;
    }
    root.dataset.mmhcAvatarBound = '1';

    var fileInput = document.getElementById('mmhcAvatarFile');
    var chooseBtn = document.getElementById('mmhcAvatarChooseBtn');
    var imgEl = document.getElementById('mmhcAvatarImg');
    var placeholderEl = document.getElementById('mmhcAvatarPlaceholder');
    var loadingEl = document.getElementById('mmhcAvatarLoading');
    var statusEl = document.getElementById('mmhcAvatarStatus');
    var uploadUrl = root.getAttribute('data-upload-url');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    function setStatus(message, type) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message;
        statusEl.classList.remove('d-none', 'is-success', 'is-error');
        if (type === 'success') {
            statusEl.classList.add('is-success');
        } else if (type === 'error') {
            statusEl.classList.add('is-error');
        }
    }

    function showPreviewFromFile(file) {
        if (!file || !imgEl) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            imgEl.src = e.target.result;
            imgEl.classList.remove('d-none');
            imgEl.removeAttribute('aria-hidden');
            if (placeholderEl) {
                placeholderEl.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    }

    function setLoading(on) {
        if (loadingEl) {
            loadingEl.classList.toggle('d-none', !on);
        }
        if (chooseBtn) {
            chooseBtn.disabled = on;
        }
        if (fileInput) {
            fileInput.disabled = on;
        }
    }

    function upload(file) {
        if (!file) {
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            setStatus('Image must be 2 MB or smaller.', 'error');
            return;
        }
        var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (allowed.indexOf(file.type) === -1) {
            setStatus('Please choose a JPG, PNG, or GIF image.', 'error');
            return;
        }

        showPreviewFromFile(file);
        setLoading(true);
        setStatus('Uploading…', null);

        var formData = new FormData();
        formData.append('avatar', file);

        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                setLoading(false);
                if (result.ok && result.data && result.data.success) {
                    if (result.data.avatar_url && imgEl) {
                        imgEl.src = result.data.avatar_url;
                        imgEl.classList.remove('d-none');
                        if (placeholderEl) {
                            placeholderEl.classList.add('d-none');
                        }
                    }
                    if (chooseBtn) {
                        chooseBtn.innerHTML = '<i class="fas fa-image me-1" aria-hidden="true"></i> Change photo';
                    }
                    setStatus(result.data.message || 'Photo updated.', 'success');
                } else {
                    setStatus((result.data && result.data.message) || 'Upload failed. Please try again.', 'error');
                }
            })
            .catch(function () {
                setLoading(false);
                setStatus('Upload failed. Check your connection and try again.', 'error');
            });
    }

    if (chooseBtn && fileInput) {
        chooseBtn.addEventListener('click', function () {
            fileInput.click();
        });
    }
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (file) {
                upload(file);
            }
            fileInput.value = '';
        });
    }
})();
</script>
