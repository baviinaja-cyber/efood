// EFOOD Geolocation & Leaflet Map Handler

let efoodMap = null;
let efoodMarker = null;

function initCheckoutMap(defaultLat, defaultLng, restaurantLat, restaurantLng, restaurantId) {
  const mapElement = document.getElementById('map-container');
  if (!mapElement) return;

  const initialLat = parseFloat(defaultLat) || -6.20000000;
  const initialLng = parseFloat(defaultLng) || 106.81666600;

  // Inisialisasi peta Leaflet
  efoodMap = L.map('map-container').setView([initialLat, initialLng], 15);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(efoodMap);

  // Custom Pin Icon untuk pelanggan (Orange Pin)
  const customerIcon = L.divIcon({
    className: 'custom-pin-customer',
    html: '<div style="background-color:#FF5722;width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #FFF;box-shadow:0 3px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-person-walking" style="transform:rotate(45deg);color:#FFF;font-size:14px;"></i></div>',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
  });

  // Tambahkan marker restoran jika koordinat restoran tersedia
  if (restaurantLat && restaurantLng) {
    const resIcon = L.divIcon({
      className: 'custom-pin-res',
      html: '<div style="background-color:#1E293B;width:28px;height:28px;border-radius:50%;border:2px solid #FFF;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-utensils" style="color:#FFF;font-size:12px;"></i></div>',
      iconSize: [28, 28],
      iconAnchor: [14, 14]
    });
    L.marker([restaurantLat, restaurantLng], { icon: resIcon })
      .addTo(efoodMap)
      .bindPopup('<b>Lokasi Restoran Mitra</b>');
  }

  // Tambahkan draggable marker untuk titik pengantaran pelanggan
  efoodMarker = L.marker([initialLat, initialLng], {
    draggable: true,
    icon: customerIcon
  }).addTo(efoodMap);

  efoodMarker.bindPopup('Geser pin ini tepat ke pintu rumah atau lokasi Anda!').openPopup();

  // Event saat pin digeser
  efoodMarker.on('dragend', function (event) {
    const position = efoodMarker.getLatLng();
    updateCoordinatesAndRecalculate(position.lat, position.lng, restaurantId);
  });

  // Event saat peta diklik
  efoodMap.on('click', function (e) {
    efoodMarker.setLatLng(e.latlng);
    updateCoordinatesAndRecalculate(e.latlng.lat, e.latlng.lng, restaurantId);
  });
}

function updateCoordinatesAndRecalculate(lat, lng, restaurantId) {
  const latInput = document.getElementById('delivery_latitude');
  const lngInput = document.getElementById('delivery_longitude');
  const coordDisplay = document.getElementById('coord-display');

  if (latInput) latInput.value = lat.toFixed(8);
  if (lngInput) lngInput.value = lng.toFixed(8);
  if (coordDisplay) coordDisplay.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

  // Hitung ulang ongkir secara dinamis via AJAX
  if (restaurantId) {
    fetch(`/checkout/calculate-fee?restaurant_id=${restaurantId}&lat=${lat}&lng=${lng}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const feeDisplay = document.getElementById('delivery-fee-display');
          const distanceDisplay = document.getElementById('distance-display');
          const grandTotalDisplay = document.getElementById('grand-total-display');
          const subtotalVal = parseFloat(document.getElementById('subtotal-val')?.value || 0);

          if (feeDisplay) feeDisplay.innerText = data.delivery_fee_formatted;
          if (distanceDisplay) distanceDisplay.innerText = `${data.distance_km} km`;
          if (grandTotalDisplay) {
            const grandTotal = subtotalVal + data.delivery_fee;
            grandTotalDisplay.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
          }
        }
      })
      .catch(err => console.error('Gagal kalkulasi ongkir:', err));
  }
}

// Handler Tombol "Gunakan Lokasi Saya" (Browser Geolocation API)
function detectMyLocation(restaurantId = null) {
  const statusBadge = document.getElementById('gps-status-badge');
  if (statusBadge) {
    statusBadge.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Mendeteksi koordinat GPS...';
    statusBadge.classList.remove('d-none');
  }

  if (!navigator.geolocation) {
    alert('Browser Anda tidak mendukung Geolocation GPS. Silakan pilih titik di peta secara manual.');
    if (statusBadge) statusBadge.classList.add('d-none');
    return;
  }

  const options = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0
  };

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const accuracy = Math.round(position.coords.accuracy);

      if (statusBadge) {
        statusBadge.className = 'badge bg-success-subtle text-success p-2 mt-2 d-inline-block';
        statusBadge.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> GPS Terdeteksi (Akurasi: ±${accuracy}m)`;
      }

      if (efoodMap && efoodMarker) {
        efoodMap.setView([lat, lng], 17);
        efoodMarker.setLatLng([lat, lng]);
        updateCoordinatesAndRecalculate(lat, lng, restaurantId);
      }

      // Coba reverse geocoding via OpenStreetMap Nominatim (gratis)
      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(res => res.json())
        .then(data => {
          const addressInput = document.getElementById('delivery_address');
          if (addressInput && !addressInput.value) {
            addressInput.value = data.display_name || '';
          }
        })
        .catch(() => {});
    },
    (error) => {
      let msg = 'Gagal mengambil lokasi GPS.';
      if (error.code === error.PERMISSION_DENIED) {
        msg = 'Izin lokasi GPS ditolak oleh pengguna. Silakan geser pin di peta untuk menentukan alamat Anda.';
      } else if (error.code === error.TIMEOUT) {
        msg = 'Pencarian sinyal GPS timeout. Silakan coba kembali atau gunakan pin peta.';
      }
      if (statusBadge) {
        statusBadge.className = 'badge bg-warning-subtle text-dark p-2 mt-2 d-inline-block';
        statusBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> ${msg}`;
      }
    },
    options
  );
}
