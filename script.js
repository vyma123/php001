

document.addEventListener('DOMContentLoaded', function () {
  const galleryImages = document.querySelectorAll(
    '.gallery-image'
  );
  const selectedGalleriesInput = document.getElementById(
    'selectedGalleries'
  );
  let selectedGalleries = [];

  galleryImages.forEach((img) => {
    img.addEventListener('click', function () {
      const id = this.dataset.id;
      if (selectedGalleries.includes(id)) {
        selectedGalleries = selectedGalleries.filter(
          (galleryId) => galleryId !== id
        );
        this.classList.remove('selected');
      } else {
        selectedGalleries.push(id);
        this.classList.add('selected');
      }
      selectedGalleriesInput.value =
        selectedGalleries.join(',');
    });
  });
});

function deleteid(id) {
  if (confirm('Sure To Remove This Record ?')) {
    window.location.href = 'delete.php?deleteid=' + id;
  }
}