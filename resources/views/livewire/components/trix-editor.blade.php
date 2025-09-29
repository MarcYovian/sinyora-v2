<div x-data="{
    isFocused: false,
    pendingAttachments: {},

    // Fungsi untuk menangani upload file langsung dari Trix
    handleFileUpload(event) {
        console.log('File upload started', event.attachment);
        if (!event.attachment.file) { return; }

        // Store attachment reference by ID
        this.pendingAttachments[event.attachment.id] = event.attachment;

        @this.upload(
            'file',
            event.attachment.file,
            (uploadedFilename) => {
                console.log('Upload completed, calling completeFileUpload with ID:', event.attachment.id);
                // Beri tahu backend untuk menyelesaikan proses setelah file ter-upload
                @this.call('completeFileUpload', event.attachment.id);
            },
            () => {
                console.log('Upload error');
                // Handle error
                event.attachment.remove();
                delete this.pendingAttachments[event.attachment.id];
            },
            (progressEvent) => {
                console.log('Upload progress:', progressEvent.detail.progress);
                // Handle progress
                event.attachment.setUploadProgress(progressEvent.detail.progress);
            }
        );
    }
}"
    @trix-upload-completed.self="
        console.log('Upload completed event received:', event.detail);
        const attachmentId = event.detail.attachmentId;
        const attachment = pendingAttachments[attachmentId];

        console.log('Found pending attachment:', attachment);

        if (attachment) {
            attachment.setAttributes({
                url: event.detail.url,
                href: event.detail.url
            });
            console.log('Attachment attributes set');
            // Clean up the pending attachment
            delete pendingAttachments[attachmentId];
        } else {
            console.log('Pending attachment not found with ID:', attachmentId);
        }
    ">
    <div x-bind:class="{
        'border-primary-500 ring-4 ring-primary-500/10': isFocused,
        'dark:border-primary-500': isFocused,
        'border-gray-300 dark:border-gray-600': !isFocused
    }"
        class="block w-full transition duration-75 rounded-lg shadow-sm bg-white dark:bg-gray-900">

        <input id="trix-{{ $this->getId() }}" type="hidden" value="{{ $value }}">

        <trix-editor x-ref="trixInput" input="trix-{{ $this->getId() }}" @focus="isFocused = true" @blur="isFocused = false"
            @trix-change="$wire.set('value', $event.target.value, false)" @trix-attachment-add="handleFileUpload"
            class="trix-content block w-full !shadow-none ring-0 border-0 rounded-lg focus:ring-0 focus:border-0 p-3 dark:bg-gray-900 dark:text-gray-200">
        </trix-editor>
    </div>
</div>
