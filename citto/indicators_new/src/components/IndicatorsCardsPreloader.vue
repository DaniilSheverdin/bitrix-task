<template>
  <div
    class="w-100 tab-pane px-3 fade active show"
    id="preloader"
    role="tabpanel"
    aria-labelledby="doc-online"
  >
    <div class="my-3 p-3 bg-white">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="80" height="80" class="cit-preloader">
        <g fill="var(--info)">
          <circle cx="57.4" cy="20" r="20"></circle>
          <circle cx="53.5" cy="63" r="17"></circle>
          <circle cx="18.5" cy="59" r="11"></circle>
          <circle cx="16.5" cy="27.5" r="14"></circle>
          <path d="M39.4,69.9c-15.9,0-28.8-12.9-28.8-28.8s12.9-28.8,28.8-28.8s28.8,12.9,28.8,28.8S55.3,69.9,39.4,69.9zM37.3,18.4c-13.2,0-24,10.8-24,24s10.8,24,24,24s24-10.8,24-24S50.5,18.4,37.3,18.4z"></path>
        </g>
      </svg>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IndicatorsCardsPreloader',
};
</script>
