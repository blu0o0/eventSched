<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button menu="main-menu" color="light">
            <ion-icon :icon="menuOutline"></ion-icon>
          </ion-menu-button>
        </ion-buttons>
        <ion-title>
          Venue Map
        </ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="venue-map-layout">
        <!-- Sidebar -->
        <div class="sidebar-container">
          <VenueSidebar
            :venues="venues"
            :venue-availability="venueAvailability"
            :selected-venue-id="selectedVenueId"
            @venue-select="onVenueSelect"
          />
        </div>

        <!-- Main Content -->
        <div class="main-content">
          <!-- Loading State -->
          <LoadingSpinner v-if="loading" message="Loading venue map..." />

          <!-- Map Container -->
          <div v-else class="map-wrapper">
            <div id="venueMap" class="map-container"></div>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardTitle,
  IonIcon,
  IonLabel,
  IonDatetimeButton,
  IonModal,
  IonDatetime,
} from '@ionic/vue';
import { useApi } from '../composables/useApi';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import VenueSidebar from '../components/VenueSidebar.vue';
import { mapOutline, calendarOutline, informationCircleOutline, menuOutline } from 'ionicons/icons';
import { Venue, Reservation } from '../types';
import { API_BASE_URL, GOOGLE_MAPS_API_KEY } from '../config/env';
import { formatTime } from '../utils/validators';

// Declare Google Maps types
declare global {
  interface Window {
    google: any;
  }
}

const { loading, execute } = useApi<any>();
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const minDate = new Date().toISOString().split('T')[0];
const map = ref<any>(null);
const markers: any[] = [];
const venues = ref<Venue[]>([]);
const venueAvailability = ref<Record<number, any>>({});
const selectedVenueId = ref<number | null>(null);

// Load last selected venue from localStorage
onMounted(() => {
  const savedVenueId = localStorage.getItem('lastSelectedVenueId');
  if (savedVenueId) {
    selectedVenueId.value = parseInt(savedVenueId);
  }
});

// Campus boundary coordinates (Santiago Campus)
const campusBoundary = [
  { lat: 16.72287, lng: 121.53544 },
  { lat: 16.72213, lng: 121.53542 },
  { lat: 16.72214, lng: 121.53735 },
  { lat: 16.72280, lng: 121.53742 }
];

function onDateChange(event: CustomEvent) {
  const date = event.detail.value as string;
  selectedDate.value = date.split('T')[0];
  loadMapData();
}

async function loadMapData() {
  try {
    const url = `${API_BASE_URL}/venues/map/data?date=${selectedDate.value}`;
    console.log('Fetching from:', url);
    
    const response = await fetch(url);
    
    // Check if response is JSON
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      console.error('Invalid JSON response:', text);
      throw new Error('Server returned invalid response. Make sure Laravel server is running at ' + API_BASE_URL);
    }
    
    const data = await response.json();
    console.log('Map data received:', data);

    if (data) {
      venues.value = data.venues;
      venueAvailability.value = data.venue_availability;
      initializeMap(data.venues, data.venue_availability, data.selected_date);
      
      // Auto-select the last saved venue after map initializes
      if (selectedVenueId.value) {
        const savedVenue = venues.value.find(v => v.id === selectedVenueId.value);
        if (savedVenue) {
          // Wait for map to finish initializing
          setTimeout(() => {
            onVenueSelect(savedVenue);
          }, 500);
        }
      }
    }
  } catch (error) {
    console.error('Error loading map data:', error);
    // You could show an error message to the user here
  }
}

function initializeMap(venues: Venue[], venueAvailability: any, selectedDateStr: string) {
  // Clear existing markers
  markers.forEach(marker => marker.setMap(null));
  markers.length = 0;

  // Initialize Google Map
  if (!map.value) {
    map.value = new window.google.maps.Map(document.getElementById('venueMap'), {
      center: { lat: 16.72249174514112, lng: 121.53739618722382 },
      zoom: 16,
      mapTypeId: 'satellite',
      zoomControl: true,
      zoomControlOptions: {
        position: window.google.maps.ControlPosition.RIGHT_CENTER
      }
    });

    // Draw campus boundary polygon
    const campusPolygon = new window.google.maps.Polygon({
      paths: campusBoundary,
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 3,
      fillColor: '#FF0000',
      fillOpacity: 0.15
    });
    campusPolygon.setMap(map.value);
  }

  // Create bounds that include campus boundary
  const bounds = new window.google.maps.LatLngBounds();
  campusBoundary.forEach((latLng) => {
    bounds.extend(latLng);
  });

  // Draw areas on map
  if (venues.length > 0 && (venues[0] as any).areas) {
    venues.forEach((venue) => {
      const venueWithAreas = venue as any;
      if (!venueWithAreas.areas) return;
      
      venueWithAreas.areas.forEach((area: any) => {
        if (!area.map_coordinates) return;
        
        const coords = area.map_coordinates.split(',');
        if (coords.length !== 2) return;
        
        const position = {
          lat: parseFloat(coords[0].trim()),
          lng: parseFloat(coords[1].trim())
        };
        
        if (isNaN(position.lat) || isNaN(position.lng)) return;
        
        bounds.extend(position);
        
        // Create area marker (square shape)
        const areaMarkerIcon = {
          path: window.google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
          scale: 8,
          fillColor: '#9c27b0',
          fillOpacity: 0.9,
          strokeColor: '#ffffff',
          strokeWeight: 2,
          rotation: 45,
          anchor: new window.google.maps.Point(0, 0)
        };
        
        const areaMarker = new window.google.maps.Marker({
          position: position,
          map: map.value,
          icon: areaMarkerIcon,
          title: area.name + ' (' + (area.venue ? area.venue.name : 'No Venue') + ')',
          zIndex: 1000
        });
        
        // Area popup content
        let areaPopupContent = `
          <div class="map-popup">
            <h6 class="popup-title">${area.name}</h6>
            <p class="popup-info"><strong>Venue:</strong> ${area.venue ? area.venue.name : 'No venue assigned'}</p>
            <p class="popup-info"><strong>Status:</strong> ${area.status.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}</p>
        `;
        
        if (area.photo_url) {
          areaPopupContent += `
            <div class="popup-photo">
              <img src="${area.photo_url}" alt="${area.name}" class="popup-image" />
            </div>
          `;
        }
        
        areaPopupContent += `</div>`;
        
        const areaInfoWindow = new window.google.maps.InfoWindow({
          content: areaPopupContent
        });
        
        areaMarker.addListener('click', function() {
          areaInfoWindow.open(map.value, areaMarker);
        });
      });
    });
  }
  
  // Process each venue
    venues.forEach((venue) => {
      const availability = venueAvailability[venue.id];
      const isAvailable = availability.is_available;
      const isCurrentlyOccupied = availability.is_currently_occupied;
      const reservations = availability.reservations;

      // Parse coordinates
      let coordinates = null;
      if (venue.map_coordinates) {
        const coords = venue.map_coordinates.split(',');
        if (coords.length === 2) {
          coordinates = {
            lat: parseFloat(coords[0].trim()),
            lng: parseFloat(coords[1].trim())
          };
        }
      }

      // Skip venues without coordinates
      if (!coordinates || isNaN(coordinates.lat) || isNaN(coordinates.lng)) {
        console.warn('Venue ' + venue.name + ' has invalid coordinates: ' + venue.map_coordinates);
        return;
      }

      // Determine marker color and status text (matching Laravel logic)
      let iconColor: string;
      let statusText: string;
      let statusBadge: string;
      
      if (isAvailable) {
        iconColor = '#28a745';
        statusText = 'Available';
        statusBadge = `<span class="badge bg-success">${statusText}</span>`;
      } else if (isCurrentlyOccupied) {
        iconColor = '#dc3545';
        statusText = 'In Use';
        statusBadge = `<span class="badge bg-danger">${statusText}</span>`;
      } else {
        iconColor = '#ffc107';
        statusText = 'Reserved';
        statusBadge = `<span class="badge bg-warning">${statusText}</span>`;
      }
    
    let popupContent = `
      <div class="map-popup">
        <h6 class="popup-title">${venue.name}</h6>
    `;

    // Add venue photo if available
    if (venue.photo_url) {
      popupContent += `
        <div class="popup-photo">
          <img src="${venue.photo_url}" alt="${venue.name}" class="popup-image" />
        </div>
      `;
    }
    
    popupContent += `
        <p class="popup-info"><strong>Location:</strong> ${venue.location}</p>
    `;

    if (venue.description) {
      const truncatedDesc = venue.description.length > 100 
        ? venue.description.substring(0, 100) + '...' 
        : venue.description;
      popupContent += `<p class="popup-description">${truncatedDesc}</p>`;
    }

    if (!isAvailable && reservations.length > 0) {
      popupContent += `
        <div class="popup-reservations">
          <strong>Reservations for this date:</strong>
          <ul class="reservation-list">
      `;
      reservations.forEach((reservation: Reservation) => {
        popupContent += `
          <li class="reservation-item">
            <strong>${reservation.title}</strong><br />
            <small>${formatTime(reservation.start_time)} - ${formatTime(reservation.end_time)}</small>
          </li>
        `;
      });
      popupContent += `</ul></div>`;
    }

    popupContent += `</div>`;

    // Create custom marker icon
    const markerIcon = {
      path: window.google.maps.SymbolPath.CIRCLE,
      scale: 15,
      fillColor: iconColor,
      fillOpacity: 1,
      strokeColor: '#ffffff',
      strokeWeight: 3,
      anchor: new window.google.maps.Point(0, 0)
    };

    // Create marker
    const marker = new window.google.maps.Marker({
      position: coordinates,
      map: map.value,
      icon: markerIcon,
      title: venue.name
    });

    // Create InfoWindow
    const infoWindow = new window.google.maps.InfoWindow({
      content: popupContent
    });

    // Add click listener to marker
    marker.addListener('click', function() {
      infoWindow.open(map.value, marker);
    });

    markers.push(marker);
    bounds.extend(coordinates);
  });

  // Fit map to show all markers and campus boundary
  map.value.fitBounds(bounds);
  
  // Limit maximum zoom to show campus area
  window.google.maps.event.addListenerOnce(map.value, 'bounds_changed', function() {
    if (map.value.getZoom() > 17) {
      map.value.setZoom(17);
    }
    if (map.value.getZoom() < 15) {
      map.value.setZoom(15);
    }
  });
}

function loadGoogleMaps() {
  // Check if Google Maps is already loaded
  if (window.google && window.google.maps) {
    loadMapData();
    return;
  }

  // Load Google Maps script
  const script = document.createElement('script');
  script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=geometry`;
  script.async = true;
  script.defer = true;
  script.onload = () => loadMapData();
  script.onerror = () => console.error('Failed to load Google Maps');
  document.head.appendChild(script);
}

onMounted(() => {
  loadGoogleMaps();
});

onUnmounted(() => {
  // Cleanup markers
  markers.forEach(marker => marker.setMap(null));
  markers.length = 0;
});

function onVenueSelect(venue: Venue): void {
  selectedVenueId.value = venue.id;
  
  // Save selected venue to localStorage
  localStorage.setItem('lastSelectedVenueId', venue.id.toString());
  
  // Parse coordinates and center map on selected venue
  if (venue.map_coordinates) {
    const coords = venue.map_coordinates.split(',');
    if (coords.length === 2) {
      const lat = parseFloat(coords[0].trim());
      const lng = parseFloat(coords[1].trim());
      
      if (!isNaN(lat) && !isNaN(lng) && map.value) {
        const position = { lat, lng };
        map.value.panTo(position);
        map.value.setZoom(17);
        
        // Find and open the marker's info window
        const marker = markers.find(m => {
          const markerPos = m.getPosition();
          return markerPos.lat() === lat && markerPos.lng() === lng;
        });
        
        if (marker) {
          window.google.maps.event.trigger(marker, 'click');
        }
      }
    }
  }
}
</script>

<style scoped>
.venue-map-layout {
  display: flex;
  height: 100%;
  width: 100%;
}

.sidebar-container {
  width: 350px;
  min-width: 350px;
  height: 100%;
  overflow: hidden;
  background: var(--ion-color-light);
  border-right: 1px solid var(--ion-color-medium);
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  gap: 1rem;
  overflow-y: auto;
  height: 100%;
  position: relative;
}

.filter-card {
  margin: 0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 1000;
  min-width: 240px;
  max-width: 280px;
}

.filter-card ion-card-content {
  padding: 8px 12px;
}

.date-filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-icon {
  font-size: 18px;
  color: var(--ion-color-primary);
}

.date-picker {
  flex: 1;
}

ion-label {
  font-size: 12px;
}

.map-wrapper {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--ion-color-light);
  flex: 1;
  position: relative;
}

.map-container {
  height: 100%;
  min-height: 500px;
  width: 100%;
  background: #f5f5f5;
  border-radius: 12px;
}

.legend-card {
  margin: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.legend-card ion-card-header {
  padding-bottom: 0.5rem;
}

.legend-icon {
  font-size: 20px;
  color: var(--ion-color-primary);
  margin-right: 0.5rem;
}

.legend-items {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  justify-content: space-around;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 14px;
  font-weight: 500;
}

.legend-badge {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.legend-badge.available {
  background-color: #28a745;
}

.legend-badge.occupied {
  background-color: #dc3545;
}

.legend-badge.no-coords {
  background-color: #6c757d;
}

.header-icon {
  margin-right: 0.5rem;
  font-size: 20px;
}

/* Map Popup Styles */
:deep(.map-popup) {
  padding: 8px;
  min-width: 250px;
  max-width: 350px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

:deep(.popup-title) {
  margin: 0 0 10px 0;
  font-weight: 700;
  color: #1f2937;
  font-size: 16px;
}

:deep(.popup-photo) {
  margin-bottom: 10px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

:deep(.popup-image) {
  width: 100%;
  max-height: 200px;
  object-fit: cover;
  display: block;
}

:deep(.popup-status),
:deep(.popup-info),
:deep(.popup-date) {
  margin: 6px 0;
  font-size: 14px;
  line-height: 1.5;
}

:deep(.popup-status strong),
:deep(.popup-info strong),
:deep(.popup-date strong) {
  font-weight: 600;
  color: #374151;
}

:deep(.popup-description) {
  margin: 8px 0;
  color: #6b7280;
  font-size: 13px;
  line-height: 1.5;
}

:deep(.popup-reservations) {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  font-size: 14px;
}

:deep(.reservation-list) {
  margin: 8px 0 0 0;
  padding-left: 20px;
  list-style-type: disc;
}

:deep(.reservation-item) {
  margin: 6px 0;
  line-height: 1.5;
}

:deep(.reservation-item strong) {
  font-weight: 600;
  color: #1f2937;
}

:deep(.reservation-item small) {
  color: #6b7280;
  font-size: 12px;
}

:deep(.badge) {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

:deep(.badge.bg-success) {
  background-color: #10b981;
  color: white;
}

:deep(.badge.bg-danger) {
  background-color: #ef4444;
  color: white;
}

@media (max-width: 768px) {
  .venue-map-layout {
    flex-direction: column;
  }

  .sidebar-container {
    width: 100%;
    min-width: 100%;
    height: auto;
    max-height: 40vh;
    border-right: none;
    border-bottom: 1px solid var(--ion-color-medium);
  }

  .main-content {
    padding: 0.75rem;
    gap: 0.75rem;
  }

  .map-container {
    height: 400px;
  }

  .legend-items {
    gap: 1rem;
  }

  .legend-item {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .sidebar-container {
    max-height: 35vh;
  }

  .main-content {
    padding: 0.5rem;
    gap: 0.5rem;
  }

  .map-container {
    height: 350px;
  }

  .filter-card {
    margin: 0 -0.5rem;
  }

  .legend-card {
    margin: 0 -0.5rem;
  }
}
</style>