<template>
  <q-page class="q-pa-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Проекты" to="/projects"/>
      <q-breadcrumbs-el :label="`Проект ${projectId}`" :to="currentProjectRooms"/>
      <q-breadcrumbs-el label="Комнаты" :to=currentProjectRooms />
      <q-breadcrumbs-el :label="`Комната ${roomId}`"/>
    </q-breadcrumbs>

    <div class="">
      <h5 class="q-my-none ">Проект "{{ roomInfo?.project_name }}" ({{ projectId }})</h5>
      <h5 class="q-my-none ">Комната "{{ roomInfo?.name }}" ({{ roomId }}) </h5>
      <template v-if="roomInfo">
        <h6 class="q-my-none "></h6>
        <h6 class="q-my-none ">{{ roomInfo.description }}</h6>
<!--        <pre v-if="roomInfo">{{ roomInfo }}</pre>-->
      </template>
    </div>

    <q-card flat bordered class="q-pa-md q-mt-md">
      <div class="text-subtitle1">Высота комнаты: <span class="text-weight-medium">{{ roomHeight }} м.</span></div>
      <div class="text-subtitle1">Периметр стен: <span class="text-weight-medium">{{ wallsPerimeter }} м.</span></div>
      <div class="text-subtitle1">Площадь стен: <span class="text-weight-medium">{{ wallsSquare }} кв. м.</span></div>
      <div class="text-subtitle1">Площадь потолка/пола: <span class="text-weight-medium">{{ ceilingSquare }} кв. м.</span></div>
    </q-card>

    <q-card flat bordered class="q-pa-md q-mt-md">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="walls" label="Стены" />
        <q-tab name="doors" label="Двери" />
        <q-tab name="windows" label="Окна" />
      </q-tabs>
      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="walls">
          <div class="text-h6">Редактирование стен</div>

          <!-- добавление точечно стены -->
          <div class="flex">
            <q-form @submit.prevent="addWall">
              <div class="row q-gutter-md">
                <q-input
                  v-model.number="length"
                  label="Длина стены (см)"
                  type="number"
                  :rules="[val => val > 0 || 'Введите длину > 0']"
                  name="wall_length"/>
                <q-input
                  v-model.number="angle"
                  label="Угол (° от X вправо, против часовой)"
                  type="number"
                  name="wall_angle"/>
                <q-btn color="primary" label="Добавить стену" type="submit"/>
                <q-btn color="negative" flat label="Удалить последнюю" @click="removeLastWall"/>
              </div>
            </q-form>
          </div>

          <q-separator class="q-my-md"/>

          <!-- добавление стен к комнате -->
          <div class="flex">
            <!-- input.textarea для ввода стен; предпросмотр, сохранение стен, инфа после рендера svg -->
            <div style="width: 333px;">
              <q-input
                name="wall_lengths"
                filled
                v-model="rawInput"
                label="Ввод стен: длина, угол, настоящая ли стена"
                type="textarea"
                autogrow
                class="q-mt-md"
                hint="Каждая строка: ДЛИНА УГОЛ НЕ_ПРОЕМ, например: 400 90 1"
              />

              <!-- кнопка сохранения стен -->
              <div class="flex items-center justify-between q-mt-md">
                <q-btn
                  class=""
                  label="Предпросмотр"
                  color="primary"
                  @click="loadWallsFromText"
                />
                <q-btn
                  class=""
                  color="primary"
                  label="Сохранить стены"
                  :loading="wallsSaving"
                  @click="saveWalls" />
              </div>

              <!-- svg rendered info -->
              <div class="q-ma-sm text-subtitle1">
                <div>
                  Периметр: {{ stats.perimeter }} см<br/>
                  Замкнута ли фигура: <strong :class="stats.is_closed ? 'text-positive' : 'text-negative'">
                  {{ stats.is_closed ? 'Да' : 'Нет' }}
                </strong>
                </div>
                <span>Конечная точка: ({{ stats.end_position[0] }}, {{ stats.end_position[1] }})</span>

                <!--  <div class="overflow-hidden q-ma-md "><pre class="q-ma-none">{{ roomWallsInfo }}</pre></div>-->
              </div>
            </div>

            <!-- нарисованный svg, zoom, периметр -->
            <div class="q-ma-md">
              <!-- сама svg -->
              <div class="col2" style="border: 1px solid #ccc;">
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

              <!-- zoom -->
              <div class="q-mt-xs q-gutter-sm">
                <q-btn icon="zoom_in" @click="zoom += 0.1" round/>
                <q-btn icon="zoom_out" @click="zoom = Math.max(0.1, zoom - 0.1)" round/>
                <span>Масштаб: {{ zoom.toFixed(1) }}x</span>
              </div>
            </div>

          </div>
        </q-tab-panel>

        <q-tab-panel name="doors">
          <div class="text-h6">Редактирование дверей</div>
        </q-tab-panel>

        <q-tab-panel name="windows">
          <div class="text-h6">Редактирование окон</div>
        </q-tab-panel>
      </q-tab-panels>

    </q-card>
  </q-page>
</template>

<script setup>
import {useRoute} from "vue-router"
import {computed, onMounted, ref} from "vue";
import {api} from "@/boot/axios.js";
import {Notify} from "quasar";

//const router = userRouter;
const route = useRoute();

const tab = ref('walls');

const projectId = ref(null);
const roomId = ref(null);
const roomInfo = ref(null);
const roomWallsInfo = ref(null);
const wallsSaving = ref(false);

const roomHeight = ref(0);
const ceilingSquare = ref(0);

const svgRef = ref(null)

const svgWidth = 1000
const svgHeight = 1000
const zoom = ref(6)
const panX = ref(-20)
const panY = ref(0)

let isPanning = false
let startPoint = { x: 0, y: 0 }

let rawInput = ref('');

// создается четырехугольник с отсечка 50, 50
rawInput.value = `350 90 1
400 0 1
50 -90 1
50 0 1
300 -90 1
450 180 1`;

// создается четырехугольник со срезом угла 50, 50
rawInput.value = `350 90 1
400 0 1
71 -45 1
300 -90 1
450 180 1`;

rawInput.value = ``;

const currentProjectRooms = computed(() => {
  return `/projects/${projectId.value}/rooms`;
});

const getRoomInfo = async(project_id, room_id) => {
  try {
    const response = await api.get(`/v1/project/${project_id}/rooms/${room_id}`);
    const data = response.data;
    roomInfo.value = data;
    return data;
  } catch (error) {
    throw error.response?.data || { message: 'Load room failed' };
  }
};

const getWallsInfo = async(room_id) => {
  try {
    const response = await api.get(`/v1/roomWalls/${room_id}`);
    const data = response.data?.data;

    // привезти к нужному виду.
    let tmpWalls = ``;
    if (data && data.length){
      for(let i of data){
        tmpWalls += `${i.length} ${i.angle} ${i.is_real} \n`;
      }
    }
    roomWallsInfo.value = tmpWalls;

    return tmpWalls;
  } catch (error) {
    throw error.response?.data || { message: 'Load roomWalls failed' };
  }
};

const saveWalls = async() => {
  wallsSaving.value = true;
  try {
    const response = await api.post(`/v1/roomWalls/${roomId.value}`,{
      walls: rawInput.value,
    });

    Notify.create({
      type: 'positive',
      message: 'Room walls stored successfully!',
    });

    return response.data?.success;
  } catch (error) {
    throw error.response?.data || { message: 'save roomWalls failed' };
  }
  finally {
    wallsSaving.value = false;
  }
}

onMounted(async  () => {
  projectId.value = route.params.projectId;
  roomId.value = route.params.roomId;
  await getRoomInfo(projectId.value, roomId.value);
  //loadWallsFromText();
  roomHeight.value = roomInfo.value.height;

  await getWallsInfo(roomId.value);
  rawInput.value = roomWallsInfo.value;
  loadWallsFromText();
});

const walls = ref([])
const length = ref(null)
const angle = ref(0)


// Центральная точка для отображения (центр SVG)
const center = {x: 0, y: 100}

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
    perimeter: ((totalLength / 100).toFixed(2)) ,
    is_closed: isClosed,
    end_position: [dx.toFixed(2), dy.toFixed(2)]
  }
})
const wallsPerimeter = computed( () => {
  return stats.value.perimeter;
});

const wallsSquare = computed( () => {
  return (wallsPerimeter.value * roomHeight.value).toFixed(2);
})

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
  .no_margin_top{
    margin-top: 0;
  }
</style>
