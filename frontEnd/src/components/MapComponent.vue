<template>
  <div id="map" style="height: 500px; width: 100%"></div>
</template>

<script>
import L from "leaflet";

export default {
  name: "MapComponent",
  props: {
    latitude: {
      type: Number,
      required: true,
    },
    longitude: {
      type: Number,
      required: true,
    },
    zoom: {
      type: Number,
      default: 13, // Default zoom level
    },
    markerText: {
      type: String,
      default: "I am here!",
    },
  },
  mounted() {
    // Initialize the map and set the view to the provided location and zoom level
    const map = L.map("map").setView(
      [this.latitude, this.longitude],
      this.zoom
    );

    // Add the tile layer
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    // Add a marker
    const marker = L.marker([this.latitude, this.longitude]).addTo(map);

    // Add a popup to the marker
    marker.bindPopup(this.markerText).openPopup();
  },
};
</script>

<style>
#map {
  border-radius: 15px;
  z-index: 0;
}
</style>
