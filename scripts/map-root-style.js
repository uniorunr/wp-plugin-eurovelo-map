const maps = Array.from(document.querySelectorAll('div[id^="eurovelo-wordpress-map-"]'));

maps.forEach((map) => {
  map.style.width = 'calc(' + window.innerWidth + 'px - 12px)';
  map.style.maxWidth = window.innerWidth + 'px';
  map.style.marginLeft = 'calc(50% - 50vw + 8px)';
  map.style.marginRight = 'calc(50% - 50vw)';
  map.style.zIndex = '0';
});

window.addEventListener('resize', function () {
  const maps = Array.from(document.querySelectorAll('div[id^="eurovelo-wordpress-map-"]'));

  maps.forEach((map) => {
    map.style.width = 'calc(' + window.innerWidth + 'px - 12px)';
    map.style.maxWidth = window.innerWidth + 'px';
    map.style.marginLeft = 'calc(50% - 50vw + 8px)';
    map.style.marginRight = 'calc(50% - 50vw)';
    map.style.zIndex = '0';
  });
});
