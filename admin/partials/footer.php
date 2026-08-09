    </div> <!-- Close flex min-h-screen -->

    <footer class="bg-white border-t border-slate-200 text-center text-xs text-slate-500 py-3 mt-auto">
        &copy; <?= date("Y") ?> Certificate Verification & Management System. All rights reserved.
    </footer>

    <!-- GLOBAL SWEETALERT2 CONFIRMATION INTERCEPTOR -->
    <script>
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a, button');
        if (!target) return;

        const confirmMsg = target.getAttribute('data-confirm');
        const onclickAttr = target.getAttribute('onclick');

        if (confirmMsg || (onclickAttr && onclickAttr.includes('confirm('))) {
            e.preventDefault();
            e.stopPropagation();

            let message = confirmMsg;
            if (!message && onclickAttr) {
                const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    message = match[1].replace(/\\'/g, "'");
                }
            }
            if (!message) message = "Are you sure you want to perform this action?";

            const href = target.getAttribute('href');
            const form = target.closest('form');

            Swal.fire({
                title: 'Confirmation',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48', // rose-600 for delete/action
                cancelButtonColor: '#64748b',  // slate-500
                confirmButtonText: 'Yes, Proceed',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl p-6 font-sans shadow-2xl',
                    confirmButton: 'px-5 py-2.5 rounded-xl text-xs font-bold shadow-md',
                    cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        window.location.href = href;
                    } else if (form) {
                        form.submit();
                    }
                }
            });
        }
    }, true);
    </script>

</body>
</html>