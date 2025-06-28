</div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById("menu-toggle").addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("wrapper").classList.toggle("toggled");
        });

        // Script untuk checkbox "Select All"
        function toggleSelectAll(source, name) {
            const checkboxes = document.getElementsByName(name);
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</body>
</html>
