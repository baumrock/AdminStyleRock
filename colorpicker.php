<style>
  .color {
    height: 40px;
    display: flex;
    align-items: center;
    padding: 0 15px;
    justify-content: space-between;

    span:last-child {
      color: white;
    }
  }

  .colors {
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
    max-width: 500px;
  }
</style>
<div class="uk-margin-bottom colors">
  <div class="color" style="background: var(--rock-primary);">
    <span>--rock-primary</span>
    <span>--rock-primary</span>
  </div>
  <div class="color" style="background: var(--rock-gray-100);">
    <span>--rock-gray-100</span>
    <span>--rock-gray-100</span>
  </div>
  <div class="color" style="background: var(--rock-gray-200);">
    <span>--rock-gray-200</span>
    <span>--rock-gray-200</span>
  </div>
  <div class="color" style="background: var(--rock-gray-300);">
    <span>--rock-gray-300</span>
    <span>--rock-gray-300</span>
  </div>
  <div class="color" style="background: var(--rock-gray-400);">
    <span>--rock-gray-400</span>
    <span>--rock-gray-400</span>
  </div>
  <div class="color" style="background: var(--rock-gray-500);">
    <span>--rock-gray-500</span>
    <span>--rock-gray-500</span>
  </div>
  <div class="color" style="background: var(--rock-gray-600);">
    <span>--rock-gray-600</span>
    <span>--rock-gray-600</span>
  </div>
  <div class="color" style="background: var(--rock-gray-700);">
    <span>--rock-gray-700</span>
    <span>--rock-gray-700</span>
  </div>
  <div class="color" style="background: var(--rock-gray-800);">
    <span>--rock-gray-800</span>
    <span>--rock-gray-800</span>
  </div>
  <div class="color" style="background: var(--rock-gray-900);">
    <span>--rock-gray-900</span>
    <span>--rock-gray-900</span>
  </div>
</div>
<div class="uk-margin-small-bottom"><input id="pick-primary" type="color" value="<?= $rockprimary ?>"> Primary Color</div>
<div><input type="range" id="saturation" min="0" max="100" value="<?= $saturation ?>" /> Saturation for grays</div>
<script>
  function hexToHSL(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

    let r = parseInt(result[1], 16);
    let g = parseInt(result[2], 16);
    let b = parseInt(result[3], 16);

    r /= 255, g /= 255, b /= 255;
    let max = Math.max(r, g, b),
      min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;

    if (max == min) {
      h = s = 0; // achromatic
    } else {
      var d = max - min;
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
      switch (max) {
        case r:
          h = (g - b) / d + (g < b ? 6 : 0);
          break;
        case g:
          h = (b - r) / d + 2;
          break;
        case b:
          h = (r - g) / d + 4;
          break;
      }

      h /= 6;
    }

    h = Math.round(h * 360);
    s = Math.round(s * 100) + "%";
    l = Math.round(l * 100) + "%";

    return {
      h,
      s,
      l
    };
  }

  // add event listeners
  document.addEventListener('DOMContentLoaded', function() {
    let html = document.querySelector('html');
    let picker = document.getElementById('pick-primary');
    let slider = document.getElementById('saturation');
    let primary = document.querySelector('input[name="rockprimary"]');
    let h = document.querySelector('input[name="rockprimary_h"]');
    let s = document.querySelector('input[name="rockprimary_s"]');
    let l = document.querySelector('input[name="rockprimary_l"]');
    let saturation = document.querySelector('input[name="saturation"]');

    // update primary field based on color picker
    picker.addEventListener('input', (e) => {
      let hex = e.target.value;
      primary.value = hex;
      primary.dispatchEvent(new Event('input'));
    });

    // update slider if saturation field was changed
    saturation.addEventListener('input', (e) => {
      let val = e.target.value.replace('%', '');
      let sat = val + '%';
      slider.value = val;
      html.style.setProperty('--rock-gray-saturation', sat);
      e.target.value = sat;
    });

    // update saturation field based on slider
    slider.addEventListener('input', (e) => {
      let sat = e.target.value + "%";
      saturation.value = sat;
      html.style.setProperty('--rock-gray-saturation', sat);
    });

    // update hsl fields based on hex field
    primary.addEventListener('input', (e) => {
      let hex = e.target.value;
      try {
        let hsl = hexToHSL(hex);
        h.value = hsl.h;
        s.value = hsl.s;
        l.value = hsl.l;

        // update live colors
        html.style.setProperty('--rock-primary-h', hsl.h);
        html.style.setProperty('--rock-primary-s', hsl.s);
        html.style.setProperty('--rock-primary-l', hsl.l);

        // update color picker
        if (picker.value !== hex) picker.value = hex;
      } catch (error) {}
    });
  });
</script>