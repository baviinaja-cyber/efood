// EFOOD Real-Time Order Status Tracker via AJAX Polling

function startOrderTracking(orderNumber, initialStatus) {
  let currentStatus = initialStatus;
  const pollInterval = 6000; // 6 detik

  const intervalId = setInterval(() => {
    // Hanya polling jika tab/window sedang aktif
    if (document.hidden) return;

    fetch(`/api/order-status/${orderNumber}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        // Jika status berubah
        if (data.status !== currentStatus) {
          currentStatus = data.status;

          // Update Status Badge di UI
          const badgeEl = document.getElementById('order-status-badge');
          if (badgeEl) {
            badgeEl.innerText = data.status_label;
            badgeEl.className = 'badge px-3 py-2 fs-6 shadow-sm ' + getBadgeClass(data.status);
          }

          // Update Driver info jika baru ditugaskan
          if (data.driver_name) {
            const driverBox = document.getElementById('driver-info-box');
            if (driverBox) {
              driverBox.classList.remove('d-none');
              const nameEl = document.getElementById('driver-name-display');
              const plateEl = document.getElementById('driver-plate-display');
              if (nameEl) nameEl.innerText = data.driver_name;
              if (plateEl) plateEl.innerText = data.driver_plate;
            }
          }

          // Update Timeline steps
          updateTimelineSteps(data.status_step);

          // Tampilkan notifikasi suara atau toast
          showToast(`Status pesanan diperbarui: ${data.status_label}`, 'success');

          // Jika status sudah final (delivered, rejected, cancelled), hentikan polling
          if (['delivered', 'rejected', 'cancelled', 'delivery_failed'].includes(data.status)) {
            clearInterval(intervalId);
          }
        }
      })
      .catch(err => console.warn('Error polling status:', err));
  }, pollInterval);
}

function updateTimelineSteps(activeStepIndex) {
  const steps = document.querySelectorAll('.timeline-step');
  steps.forEach(step => {
    const stepIdx = parseInt(step.getAttribute('data-step') || '0');
    step.classList.remove('active', 'completed');

    if (stepIdx < activeStepIndex) {
      step.classList.add('completed');
    } else if (stepIdx === activeStepIndex) {
      step.classList.add('active');
    }
  });
}

function getBadgeClass(status) {
  const map = {
    'pending_verification': 'bg-warning text-dark',
    'verified': 'bg-info text-dark',
    'restaurant_processing': 'bg-primary text-white',
    'ready_for_delivery': 'bg-purple text-white',
    'assigned_to_driver': 'bg-secondary text-white',
    'picked_up': 'bg-info text-white',
    'on_delivery': 'bg-warning text-dark',
    'delivered': 'bg-success text-white',
    'rejected': 'bg-danger text-white',
    'cancelled': 'bg-dark text-white',
    'delivery_failed': 'bg-danger text-white'
  };
  return map[status] || 'bg-secondary text-white';
}
