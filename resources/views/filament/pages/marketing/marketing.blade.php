<x-filament-panels::page>
    <!-- Dekstop Layout -->
    <div class="embed-container desktop-only">
        <iframe src="https://lookerstudio.google.com/embed/reporting/f3267b1d-3aa0-4aea-9e60-a26f09cef161/page/p_4n7z5l8i5c" frameborder="0" allowfullscreen style="width: 100%; height: 100vh;"></iframe>
    </div>

    <!-- Mobile Layout -->
    <div class="embed-container mobile-only">
        <iframe src="https://lookerstudio.google.com/embed/reporting/33540533-949d-486f-b09e-332686c5c1e2/page/srJEE/" frameborder="0" allowfullscreen style="width: 100%; height: 100vh;"></iframe>
    </div>

    <!-- Tambahkan CSS di sini -->
    <style>
        .desktop-only {
            display: block;
        }

        .mobile-only {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .desktop-only {
                display: none;
            }

            .mobile-only {
                display: block;
            }
        }
    </style>
</x-filament-panels::page>