<template>
  <q-page class="q-pa-md">
    <q-card flat bordered class="q-pa-md">

      <div class="flex">
        <q-form @submit.prevent="addWall">
          <div class="row q-gutter-md">
            <q-input
              v-model.number="length"
              label="Длина стены (см)"
              type="number"
              :rules="[val => val > 0 || 'Введите длину > 0']"
            />
            <q-input
              v-model.number="angle"
              label="Угол (° от X вправо, против часовой)"
              type="number"
            />
            <q-btn color="primary" label="Добавить стену" type="submit"/>
            <q-btn color="negative" flat label="Удалить последнюю" @click="removeLastWall"/>
          </div>
        </q-form>
        <div class="q-mt-md q-gutter-sm">
          <q-btn icon="zoom_in" @click="zoom += 0.1" round/>
          <q-btn icon="zoom_out" @click="zoom = Math.max(0.1, zoom - 0.1)" round/>
          <span>Масштаб: {{ zoom.toFixed(1) }}x</span>
        </div>
      </div>
      <div>
        <q-input
          filled
          v-model="rawInput"
          label="Ввод стен: длина угол"
          type="textarea"
          autogrow
          class="q-mt-md"
          hint="Каждая строка: ДЛИНА УГОЛ, например: 400 90"
        />

        <q-btn
          class="q-mt-md"
          label="Загрузить"
          color="primary"
          @click="loadWallsFromText"
        />
      </div>

      <q-separator class="q-my-md"/>

      <div style="border: 1px solid #ccc; overflow: hidden; width: 100%; height: 400px;">
        <svg
          width="100%"
          height="100%"
          @mousedown="startPan"
          @mousemove="onPan"
          @mouseup="stopPan"
          @mouseleave="stopPan"
          @wheel.prevent="onWheel"
          :viewBox="`${panX} ${panY} ${svgWidth / zoom} ${svgHeight / zoom}`"
          ref="svgRef"
        >
          <g>
            <!-- Линии -->
            <polyline
              :points="svgPoints"
              fill="none"
              stroke="black"
              stroke-width="2"
            />

            <!-- Конечная точка -->
            <circle
              :cx="endPoint.x"
              :cy="endPoint.y"
              r="5"
              :fill="stats.is_closed ? 'green' : 'red'"
            />

            <!-- Подписи к стенам -->
            <template v-for="(p, i) in points.slice(0, -1)" :key="'label-' + i">
              <text
                :x="(p.x + points[i + 1].x) / 2"
                :y="(p.y + points[i + 1].y) / 2 - 5"
                font-size="12"
                fill="blue"
                text-anchor="middle"
              >
                {{ walls[i].length }} см
              </text>
            </template>

            <!-- Углы -->
<!--            <template v-for="(p, i) in points" :key="'angle-' + i">-->
<!--              <text-->
<!--                v-if="i > 0 && i < points.length - 1"-->
<!--                :x="p.x + 10"-->
<!--                :y="p.y - 10"-->
<!--                font-size="12"-->
<!--                fill="orange"-->
<!--              >-->
<!--                {{ getCornerAngle(i) }}°-->
<!--              </text>-->
<!--            </template>-->
          </g>
        </svg>


      </div>

      <q-separator class="q-my-md"/>

      <div class="text-subtitle1">
        Периметр: {{ stats.perimeter }} см<br/>
        Замкнута ли фигура: <strong :class="stats.is_closed ? 'text-positive' : 'text-negative'">
        {{ stats.is_closed ? 'Да' : 'Нет' }}
      </strong><br/>
        Конечная точка: ({{ stats.end_position[0] }}, {{ stats.end_position[1] }})
      </div>

    </q-card>
  </q-page>
</template>

<script setup>
import {ref, computed} from 'vue'

const walls = ref([])
const length = ref(null)
const angle = ref(0)


// Центральная точка для отображения (центр SVG)
const center = {x: 0, y: 100}

const rawInput = ref(`350 90
400 0
50 -90
50 0
300 -90
450 180`)

const loadWallsFromText = () => {
  const newWalls = []

  const lines = rawInput.value.trim().split('\n')
  for (const line of lines) {
    const [lengthStr, angleStr] = line.trim().split(/\s+/)
    const length = parseFloat(lengthStr)
    const angle = parseFloat(angleStr)
    if (!isNaN(length) && !isNaN(angle)) {
      newWalls.push({ length, angle })
    }
  }

  walls.value = newWalls
}

const addWall = () => {
  walls.value.push({
    length: parseFloat(length.value),
    angle: parseFloat(angle.value)
  })
  length.value = null
  angle.value = 0
}

const removeLastWall = () => {
  walls.value.pop()
}

// получить углы
// const getCornerAngle = (i) => {
//   if (i <= 0 || i >= points.value.length - 1) return ''
//
//   const A = points.value[i - 1]
//   const B = points.value[i]
//   const C = points.value[i + 1]
//
//   const v1 = {x: A.x - B.x, y: A.y - B.y}
//   const v2 = {x: C.x - B.x, y: C.y - B.y}
//
//   const dot = v1.x * v2.x + v1.y * v2.y
//   const len1 = Math.sqrt(v1.x * v1.x + v1.y * v1.y)
//   const len2 = Math.sqrt(v2.x * v2.x + v2.y * v2.y)
//
//   const angleRad = Math.acos(dot / (len1 * len2))
//   const angleDeg = (angleRad * 180) / Math.PI
//
//   return angleDeg.toFixed(1)
// }

// Пересчёт всех точек на основе углов и длин
const points = computed(() => {
  let x = center.x
  let y = center.y
  const pts = [{x, y}]
  //let dxTotal = 0
  //let dyTotal = 0

  for (const wall of walls.value) {
    const rad = wall.angle * Math.PI / 180
    const dx = wall.length * Math.cos(rad)
    const dy = wall.length * Math.sin(rad)

    //dxTotal += dx
    //dyTotal += dy

    x += dx * 0.2 // масштаб
    y -= dy * 0.2
    pts.push({x, y})
  }

  return pts
})

// SVG строка типа "x1,y1 x2,y2 ..."
const svgPoints = computed(() =>
  points.value.map(p => `${p.x},${p.y}`).join(' ')
)

const endPoint = computed(() => points.value[points.value.length - 1])

const stats = computed(() => {
  const totalLength = walls.value.reduce((sum, w) => sum + w.length, 0)
  const start = points.value[0]
  const end = points.value[points.value.length - 1]

  const dx = (end.x - start.x) / 0.2
  const dy = (start.y - end.y) / 0.2

  const isClosed = Math.abs(dx) < 1 && Math.abs(dy) < 1

  return {
    perimeter: totalLength.toFixed(2),
    is_closed: isClosed,
    end_position: [dx.toFixed(2), dy.toFixed(2)]
  }
})

const svgRef = ref(null)

const svgWidth = 1000
const svgHeight = 1000
const zoom = ref(1)
const panX = ref(0)
const panY = ref(0)

let isPanning = false
let startPoint = { x: 0, y: 0 }

const startPan = (e) => {
  isPanning = true
  startPoint = { x: e.clientX, y: e.clientY }
}

const onPan = (e) => {
  if (!isPanning) return
  const dx = (e.clientX - startPoint.x) / zoom.value
  const dy = (e.clientY - startPoint.y) / zoom.value
  panX.value -= dx
  panY.value -= dy
  startPoint = { x: e.clientX, y: e.clientY }
}

const stopPan = () => {
  isPanning = false
}

const onWheel = (e) => {
  const delta = e.deltaY > 0 ? -0.1 : 0.1
  zoom.value = Math.max(0.2, zoom.value + delta)
}

</script>

<style scoped>
svg {
  background: #fdfdfd;
}
</style>
