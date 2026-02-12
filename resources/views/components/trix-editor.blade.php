@props(['entangle', 'allowFileUploads' => false])

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.0-alpha.1/dist/trix.css">
    <script src="https://unpkg.com/trix@2.0.0-alpha.1/dist/trix.umd.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    @vite(['resources/css/trix.css'])
@endpush

<div wire:ignore x-data="{ value: @entangle($entangle).live }" x-init="$refs.trix.editor.loadHTML(value)" x-id="['trix']" @trix-change="value = $refs.input.value"
    @if (!$allowFileUploads) @trix-file-accept.prevent @endif class="trix-container">
    <input x-ref="input" type="hidden" :id="$id('trix')">

    <trix-editor x-ref="trix" :input="$id('trix')" class="prose"></trix-editor>
</div>

@if ($allowFileUploads)
    <script>
        // Allowed file types for Trix attachments
        const ALLOWED_TRIX_TYPES = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        const MAX_TRIX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

        // Client-side validation before upload
        addEventListener("trix-file-accept", function(event) {
            const file = event.file;

            if (!ALLOWED_TRIX_TYPES.includes(file.type)) {
                event.preventDefault();
                alert("Jenis file ini tidak diizinkan. Format yang diterima: JPG, PNG, GIF, WebP, SVG, PDF, DOC, DOCX, XLS, XLSX.");
                return;
            }

            if (file.size > MAX_TRIX_FILE_SIZE) {
                event.preventDefault();
                alert("Ukuran file terlalu besar. Maksimal 5MB.");
                return;
            }
        });

        addEventListener("trix-attachment-add", async function(event) {
            // Only process new uploads (not pre-existing attachments)
            if (!event.attachment.file) return;

            const formData = new FormData();
            formData.append("attachment", event.attachment.file);

            const setProgress = (progress) => {
                event.attachment.setUploadProgress(progress);
            };

            const setAttributes = (attributes) => {
                event.attachment.setAttributes(attributes);
            };

            try {
                let response = await axios.post(
                    '{{ route('trix-file-upload') }}',
                    formData, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        onUploadProgress: function(progressEvent) {
                            const progress = (progressEvent.loaded / progressEvent.total) * 100;
                            setProgress(progress);
                        },
                    },
                );

                setAttributes(response.data);
            } catch (error) {
                event.attachment.remove();
                const message = error.response?.data?.error || error.response?.data?.message || 'Gagal mengupload file.';
                alert(message);
                console.error('Trix upload error:', error);
            }
        });

        addEventListener("trix-attachment-remove", async function(event) {
            const attachment = event.attachment;
            const attachmentUrl = attachment.attachment.attributes.values.url;

            if (attachmentUrl) {
                try {
                    const decodedUrl = decodeURIComponent(attachmentUrl.replace(/\\\//g, '/'));
                    await axios.delete('{{ route('trix-file-delete') }}', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        data: {
                            url: decodedUrl
                        }
                    });
                } catch (error) {
                    console.error('Error deleting attachment:', error);
                }
            }
        });
    </script>
@endif
