import { ref } from 'vue';

export const isPlaying = ref(false);
export const currentSong = ref({
  title: 'Nombre de la canción',
  artist: 'Artista',
  image: 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=100&h=100&fit=crop'
});

export const queue = ref([]);
export const currentIndex = ref(-1);

export function playSong(song, newQueue = null) {
  currentSong.value = song;
  isPlaying.value = true;
  if (newQueue && Array.isArray(newQueue)) {
    queue.value = newQueue;
    currentIndex.value = newQueue.findIndex(s => s.id === song.id);
  }
}

export function playNext(shuffle = false) {
  if (queue.value.length === 0) return;
  let nextIdx = currentIndex.value + 1;
  if (shuffle) {
    nextIdx = Math.floor(Math.random() * queue.value.length);
  } else if (nextIdx >= queue.value.length) {
    nextIdx = 0;
  }
  
  if (queue.value[nextIdx]) {
    currentSong.value = queue.value[nextIdx];
    currentIndex.value = nextIdx;
    isPlaying.value = true;
  }
}

export function playPrev() {
  if (queue.value.length === 0) return;
  let prevIdx = currentIndex.value - 1;
  if (prevIdx < 0) {
    prevIdx = queue.value.length - 1;
  }
  
  if (queue.value[prevIdx]) {
    currentSong.value = queue.value[prevIdx];
    currentIndex.value = prevIdx;
    isPlaying.value = true;
  }
}
