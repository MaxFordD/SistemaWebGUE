(function () {
  var input = document.getElementById('archivos');
  var list = document.getElementById('archivos-list');
  if (!input || !list) return;
  input.addEventListener('change', function () {
    list.innerHTML = '';
    if (!this.files || this.files.length === 0) return;
    Array.prototype.slice.call(this.files).forEach(function (file) {
      var li = document.createElement('li');
      var sizeKb = (file.size / 1024).toFixed(1);
      li.textContent = file.name + ' (' + sizeKb + ' KB)';
      list.appendChild(li);
    });
  });
})();
