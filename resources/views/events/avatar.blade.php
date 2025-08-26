<x-app-layout>
    {{-- Cropper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Display Picture — {{ $event->name }}
            </h2>
            <a href="{{ route('events.show', $event->id) }}"
               class="text-sm text-gray-600 hover:text-gray-800 underline">Back to event</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Tools --}}
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Upload your photo</label>
                        <input type="file" id="userImageInput" accept="image/*" class="mt-1 w-full rounded-lg border-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Move/zoom in the circle, then “Use photo”.</p>
                    </div>

                    <div class="pt-2 border-t">
                        <button id="btnDownload"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition disabled:opacity-50"
                                disabled>
                            Download PNG
                        </button>
                        <p class="text-xs text-gray-500 mt-2">Image is created in your browser.</p>
                    </div>
                </div>
            </div>

            {{-- Right: Canvas --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                    <div class="mb-3 text-sm text-gray-600">
                        Background is the event’s avatar. Your photo will start centered — drag to reposition or use the handles to resize.
                    </div>
                    <div id="canvas-wrap" class="w-full overflow-auto rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3">
                        <canvas id="avatar-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cropper Modal (mounted to body at runtime) --}}
    <div id="cropperModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[1000]">
        <div class="bg-white rounded-2xl shadow-xl mx-4 overflow-hidden"
             style="width:min(92vw, 900px)">
            <div class="p-3 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Position your photo inside the circle</h3>
                <button id="cropCancel" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
            </div>

            <div class="modal-body" id="cropArea">
                <img id="cropperImage" alt="Crop">
                {{-- Circle overlay (only this is visible; Cropper’s square UI is hidden by CSS) --}}
                <div class="overlay-circle absolute inset-0">
                    <svg width="100%" height="100%">
                        <defs>
                            <mask id="circle-cutout">
                                <rect x="0" y="0" width="100%" height="100%" fill="white"/>
                                {{-- Center of the circle. If your ring sits a bit higher, change cy="50%" to cy="44%". --}}
                                <circle cx="50%" cy="50%" r="35%" fill="black"/>
                            </mask>
                        </defs>
                        <rect x="0" y="0" width="100%" height="100%" fill="rgba(0,0,0,0.55)" mask="url(#circle-cutout)"/>
                    </svg>
                </div>
            </div>

            <div class="p-3 border-t flex items-center justify-end gap-2">
                <button id="cropUse"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                    Use photo
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Robust crop area sizing across screens (no flex here) */
        #cropperModal .modal-body {
            height: clamp(460px, 80vh, 900px);
            position: relative;
            background: #000;
        }
        /* Make Cropper fill the container */
        #cropperModal .modal-body .cropper-container { width: 100% !important; height: 100% !important; }
        #cropperModal .modal-body img#cropperImage { max-width: 100%; max-height: 100%; display: block; }

        /* Show ONLY our circle overlay — hide Cropper’s square UI */
        #cropperModal .overlay-circle { pointer-events: none; }
        #cropperModal .cropper-modal { background: transparent !important; opacity: 0 !important; }
        #cropperModal .cropper-view-box { outline: none !important; box-shadow: none !important; }
        #cropperModal .cropper-dashed, #cropperModal .cropper-line {
            border: none !important; background: transparent !important;
        }
        /* keep handles hidden, but keep the face so it can capture drags */
        #cropperModal .cropper-point { background: transparent !important; display: none !important; }
        #cropperModal .cropper-face  { background: transparent !important; display: block !important; }

    </style>

    {{-- Libs BEFORE our custom script --}}
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <script>
        (function () {
            const bgUrl = @json(asset('storage/' . $event->avatar_url));

            // Fabric canvas (event avatar as background)
            const canvas = new fabric.Canvas('avatar-canvas', { selection: false, preserveObjectStacking: true });
            let bgImg = null;        // original background image
            let userImgObj = null;   // your cropped circle the user adds

            // Fit the canvas to the wrapper and scale bg to fill it 1:1
            function resizeCanvas() {
                if (!bgImg) return;

                const wrap = document.getElementById('canvas-wrap');
                // Available width (cap to something sensible so it doesn’t grow forever)
                const wrapW = Math.max(280, Math.min(900, wrap.clientWidth || 0));
                const ratio = bgImg.height / bgImg.width;
                const cw = Math.round(wrapW);
                const ch = Math.round(wrapW * ratio);

                // iOS crispness: backstore pixels = css pixels * DPR, then zoom to DPR
                const dpr = Math.max(1, window.devicePixelRatio || 1);

                // Set CSS size
                canvas.setDimensions({ width: cw, height: ch }, { cssOnly: true });
                // Set backing store size
                canvas.setDimensions({ width: cw * dpr, height: ch * dpr }, { backstoreOnly: true });
                canvas.setZoom(dpr);

                // Scale bg to exactly fill canvas
                const scaleX = cw / bgImg.width;
                const scaleY = ch / bgImg.height;
                canvas.setBackgroundImage(bgImg, canvas.requestRenderAll.bind(canvas), {
                originX: 'left',
                originY: 'top',
                scaleX,
                scaleY,
                });

                // Keep user photo centered after resizes (don’t change the user’s scale)
                if (userImgObj) {
                userImgObj.set({
                    left: canvas.getWidth() / 2,
                    top:  canvas.getHeight() / 2,
                });
                userImgObj.setCoords();
                }

                canvas.requestRenderAll();
            }

            // Throttled resize handler for rotation/viewport changes
            let rAF;
            window.addEventListener('resize', () => {
                cancelAnimationFrame(rAF);
                rAF = requestAnimationFrame(resizeCanvas);
            });

            // Load the background, then do the first layout
            fabric.Image.fromURL(bgUrl, (img) => {
                bgImg = img;
                resizeCanvas();
            }, { crossOrigin: 'anonymous' });

            // Elements
            const input = document.getElementById('userImageInput');
            const btnDownload = document.getElementById('btnDownload');
            const modal = document.getElementById('cropperModal');
            const cropImg = document.getElementById('cropperImage');
            const cropUse = document.getElementById('cropUse');
            const cropCancel = document.getElementById('cropCancel');

            let cropper = null;

            function openModal() {
                if (modal.parentElement !== document.body) document.body.appendChild(modal);
                modal.classList.remove('hidden'); modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
            function closeModal() {
                modal.classList.add('hidden'); modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            // Fit image to container and center the (hidden) square crop box to match our circle
            function fitCropperToContainer(startOut = true) {
                if (!cropper) return;
                const c = cropper.getContainerData();
                const i = cropper.getImageData();
                if (!c.width || !c.height || !i.naturalWidth || !i.naturalHeight) return;

                const fitScale = Math.min(c.width / i.naturalWidth, c.height / i.naturalHeight);
                cropper.reset();
                const startScale = startOut ? fitScale * 0.85 : fitScale;
                cropper.zoomTo(startScale, { x: c.width / 2, y: c.height / 2 });

                const size = Math.min(c.width, c.height) * 0.7; // circle r="35%" ≈ 70% diameter
                cropper.setCropBoxData({
                    width: size, height: size,
                    left: (c.width - size) / 2,
                    top:  (c.height - size) / 2
                });
            }

            // Open cropper on file select
            input.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const url = URL.createObjectURL(file);
                cropImg.onload = () => {
                    openModal();

                    // Wait a frame so modal has correct size
                    requestAnimationFrame(() => {
                        if (cropper) cropper.destroy();

                        cropper = new Cropper(cropImg, {
                            // IMPORTANT: allow zooming/panning more than the container bounds
                            viewMode: 0,
                            center: false,
                            dragMode: 'move',
                            movable: true,
                            zoomable: true,
                            zoomOnWheel: true,
                            zoomOnTouch: true,
                            wheelZoomRatio: 0.1,
                            background: false,
                            guides: false,
                            highlight: false,
                            toggleDragModeOnDblclick: false,
                            aspectRatio: 1,
                            autoCrop: true,
                            autoCropArea: 0.7,
                            cropBoxMovable: false,
                            cropBoxResizable: false,

                            // Let users zoom out further but not to infinity
                            zoom(event) {
                                const c = cropper.getContainerData();
                                const i = cropper.getImageData();
                                const fit = Math.min(c.width / i.naturalWidth, c.height / i.naturalHeight);
                                const minRatio = fit * 0.6; // allow up to 60% of "fit" (more zoom-out)
                                if (event.detail.ratio < minRatio) {
                                    event.preventDefault();
                                    cropper.zoomTo(minRatio);
                                }
                            },

                            ready() { fitCropperToContainer(true); }
                        });
                    });
                };
                cropImg.src = url;
            });

            // Keep it correct when window size changes
            window.addEventListener('resize', () => {
                if (!cropper) return;
                requestAnimationFrame(() => fitCropperToContainer(false));
            });

            // Cancel crop
            cropCancel.addEventListener('click', () => {
                try { cropper && cropper.destroy(); } catch {}
                cropper = null;
                closeModal();
                input.value = '';
            });

            // Confirm / Use photo -> circular PNG -> add to Fabric (draggable + uniform-resize)
            cropUse.addEventListener('click', () => {
                if (!cropper) return;

                const SIZE = 900; // export resolution for the user circle
                const square = cropper.getCroppedCanvas({
                    width: SIZE, height: SIZE,
                    imageSmoothingEnabled: true, imageSmoothingQuality: 'high'
                });

                // Mask into a circle
                const circleCanvas = document.createElement('canvas');
                circleCanvas.width = SIZE; circleCanvas.height = SIZE;
                const ctx = circleCanvas.getContext('2d');
                ctx.clearRect(0, 0, SIZE, SIZE);
                ctx.beginPath(); ctx.arc(SIZE/2, SIZE/2, SIZE/2, 0, Math.PI*2); ctx.closePath();
                ctx.clip();
                ctx.drawImage(square, 0, 0, SIZE, SIZE);

                const dataUrl = circleCanvas.toDataURL('image/png');

                // Size to your ring (tweak ratio if your ring is larger/smaller)
                const targetDiameterRatio = 0.36; // try 0.32–0.45 to match your template
                const targetDiameter = Math.min(canvas.getWidth(), canvas.getHeight()) * targetDiameterRatio;

                fabric.Image.fromURL(dataUrl, (img) => {
                    const scale = targetDiameter / img.width; // width == height
                    img.scale(scale);
                    img.set({
                        left: canvas.getWidth() / 2,
                        top: canvas.getHeight() / 2,
                        originX: 'center',
                        originY: 'center',
                        selectable: true,
                        hasControls: true,
                        hasBorders: true,
                        cornerStyle: 'circle',
                        borderColor: '#6366f1',
                        cornerColor: '#6366f1',
                        cornerSize: 10,
                        lockUniScaling: true, // keep perfectly round while resizing
                    });

                    if (userImgObj) canvas.remove(userImgObj);
                    userImgObj = img;
                    canvas.add(userImgObj).setActiveObject(userImgObj);
                    userImgObj.bringToFront();
                    canvas.renderAll();

                    btnDownload.disabled = false;
                });

                try { cropper.destroy(); } catch {}
                cropper = null;
                closeModal();
            });

            // Download final composite
            btnDownload.addEventListener('click', () => {
                canvas.discardActiveObject(); canvas.renderAll();
                const dataURL = canvas.toDataURL({ format: 'png', quality: 1 });
                const fileNameBase = @json(\Illuminate\Support\Str::slug($event->name) ?: 'event');
                const a = document.createElement('a');
                a.href = dataURL;
                a.download = `${fileNameBase}_display_picture.png`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            });
        })();
    </script>
</x-app-layout>
