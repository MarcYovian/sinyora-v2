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
        addEventListener("trix-attachment-add", async function(event) {
            const formData = new FormData();
            formData.append("attachment", event.attachment.file);

            const setProgress = (progress) => {
                event.attachment.setUploadProgress(progress);
            };

            const setAttributes = (attributes) => {
                event.attachment.setAttributes(attributes);
            };

            let response = await axios.post(
                '{{ route('trix-file-upload') }}',
                formData, {
                    onUploadProgress: function(progressEvent) {
                        const progress = (progressEvent.loaded / progressEvent.total) * 100;
                        setProgress(progress);
                    },
                },
            );

            setAttributes(response.data);
        });

        addEventListener("trix-attachment-remove", async function(event) {
            const attachment = event.attachment;
            const attachmentUrl = attachment.attachment.attributes.values.url;

            if (attachmentUrl) {
                try {
                    const decodedUrl = decodeURIComponent(attachmentUrl.replace(/\\\//g, '/'));
                    await axios.delete('{{ route('trix-file-delete') }}', {
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
