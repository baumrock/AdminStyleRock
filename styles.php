<style>
  html {
    --rock-primary-h: <?= $h ?>;
    --rock-primary-s: <?= $s ?>;
    --rock-primary-l: <?= $l ?>;
    --rock-gray-saturation: <?= $saturation ?>;

    --rock-primary: hsl(var(--rock-primary-h),
        var(--rock-primary-s),
        var(--rock-primary-l));

    --rock-gray-100: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 95%);
    --rock-gray-200: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 85%);
    --rock-gray-300: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 75%);
    --rock-gray-400: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 65%);
    --rock-gray-500: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 55%);
    --rock-gray-600: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 45%);
    --rock-gray-700: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 35%);
    --rock-gray-800: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 25%);
    --rock-gray-900: hsl(var(--rock-primary-h), var(--rock-gray-saturation), 15%);
  }
</style>