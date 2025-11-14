<template>
  <q-page class="q-pa-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Проекты" to="/projects"/>
      <q-breadcrumbs-el :label="`Проект ${projectId}`" :to="currentProjectRooms"/>
      <q-breadcrumbs-el label="Комнаты" :to=currentProjectRooms />
      <q-breadcrumbs-el :label="`Комната ${roomId}`"/>
    </q-breadcrumbs>

    <!-- for dev -->
    <div v-if="1===2">
      <pre>{{ walls }}</pre>
    </div>

    <div id="Project_titles">
      <h5 class="q-my-none ">Проект "{{ roomInfo?.project_name }}" ({{ projectId }})</h5>
      <h5 class="q-my-none ">Комната "{{ roomInfo?.name }}" ({{ roomId }}) </h5>
      <template v-if="roomInfo">
        <h6 class="q-my-none "></h6>
        <h6 class="q-my-none ">{{ roomInfo.description }}</h6>
        <!--        <pre v-if="roomInfo">{{ roomInfo }}</pre>-->
      </template>
    </div>

    <q-card id="main_room_info__characteristics-total_cost" flat bordered class="q-pa-md q-mt-md flex flex-wrap justify-between">
      <q-card class="">
        <div class="text-subtitle1 text-weight-bold">Характеристики комнаты</div>
        <div class="text-subtitle1">Высота: <span class="text-weight-medium">{{ roomHeight }} м.</span></div>
        <div class="text-subtitle1">Периметр: <span class="text-weight-medium">{{ wallsPerimeter }} м.</span></div>
        <div class="text-subtitle1">Площадь стен: <span class="text-weight-medium">{{ wallsSquare }} м.кв. </span></div>
        <div class="text-subtitle1">Площадь потолка: <span class="text-weight-medium">{{ ceilingSquare }} м.кв. </span></div>
        <div class="text-subtitle1">Площадь пола: <span class="text-weight-medium">{{ ceilingSquare }} м.кв. </span></div>
      </q-card>
      <q-card class="">
        <div class="text-subtitle1 text-weight-bold">Общая стоимость: <span class="text-weight-medium">235 000 <span v-html="rrub"></span> </span></div>
        <div class="text-subtitle1">Строительные материалы: <span class="text-weight-medium">110 000 <span v-html="rrub"></span></span></div>
        <div class="text-subtitle1">Работа мастеров: <span class="text-weight-medium">125 000 <span v-html="rrub"></span></span></div>
      </q-card>
    </q-card>

    <div id="main_expansion_tabs" class="q-mt-md">
      <q-list bordered class="rounded-borders">
        <!-- Характеристики комнаты -->
        <q-expansion-item
          expand-separator
          icon="room_preferences"
          label="Характеристики комнаты"
          caption="Стены, двери, окна"
        >
          <q-card flat bordered class="q-pa-md q-mt-md">
            <q-tabs
              v-model="room_chars_tabs"
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

            <q-tab-panels v-model="room_chars_tabs" animated>
              <q-tab-panel name="walls">
                <div class="text-h6">Редактирование стен</div>

                <!-- добавление точечно стены -->
                <div class="flex">
                  <q-form @submit.prevent="addWall">
                    <div class="row q-gutter-md">
                      <q-input
                        v-model.number="tmpWall.length"
                        label="Длина стены (см)"
                        type="number"
                        :rules="[val => val > 0 || 'Введите длину > 0']"
                        name="wall_length"/>
                      <q-input
                        v-model.number="tmpWall.angle"
                        label="Угол (° от X вправо, против часовой)"
                        type="number"
                        name="wall_angle"/>
                      <q-input
                        v-model.number="tmpWall.is_real"
                        label="Настоящая стена?"
                        type="number"
                        :rules="[val => val >= 0 && val <= 1 || 'Если не проем 1, иначе 0']"
                        name="wall_is_real"/>
                      <div>
                        <q-btn color="primary" label="Добавить стену" type="submit"/>
                        <q-btn color="negative" flat label="Удалить последнюю" @click="removeLastWall"/>
                        <q-btn color="success" flat label="Сбросить к эталону" @click="resetWalls"/>
                      </div>
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
                      v-model="wallText"
                      label="Ввод стен: длина, угол, настоящая ли стена"
                      type="textarea"
                      autogrow
                      class="q-mt-md"
                      hint="Каждая строка: ДЛИНА УГОЛ НЕ_ПРОЕМ, например: 400 90 1"
                      disable
                    />

                    <!-- кнопка сохранения стен -->
                    <div class="flex items-center justify-between q-mt-md">
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
                        <!--  Периметр: {{ stats.perimeter }} м.<br/>-->
                        <span>
                    Замкнута ли фигура:
                    <strong
                      style="display: inline-block;"
                      :class="[
                          stats.is_closed ? 'text-positive' : 'text-negative',
                          { 'animated shake': !stats.is_closed }
                        ]"
                    >
                      {{ stats.is_closed ? 'Да' : 'Нет' }}
                    </strong>
                  </span>
                      </div>

                      <span>Конечная точка: ({{ stats.end_position[0] }}, {{ stats.end_position[1] }})</span>

                      <!--                  <div class="overflow-hidden q-ma-md "><pre class="q-ma-none">{{ etalonWalls }}</pre></div>-->
                      <!--                  <div>{{ walls }}</div>-->
                    </div>
                  </div>

                  <!-- нарисованный svg, zoom, периметр -->
                  <div class="q-ma-md">
                    <!-- сама svg -->
                    <div class="col2" style="border: 1px solid #ccc;">
                      <svg
                        width="350px"
                        height="230px"
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
                          <template
                            v-if="showAnglesValues"
                          >
                            <template v-for="(p, i) in points" :key="'angle-' + i">
                              <text
                                v-if="i > 0 && i < points.length - 1"
                                :x="p.x - 25"
                                :y="p.y + 15"
                                font-size="9"
                                fill="orange"
                              >
                                {{ getCornerAngle(i) }}°
                              </text>
                            </template>
                          </template>
                        </g>
                      </svg>
                    </div>

                    <!-- zoom -->
                    <div class="flex flex-row items-center q-mt-md">
                      <div class="flex flex-row items-center q-gutter-sm">
                        <q-btn icon="zoom_in" @click="zoom += 0.1" round/>
                        <q-btn icon="zoom_out" @click="zoom = Math.max(0.1, zoom - 0.1)" round/>
                        <span>Масштаб: {{ zoom.toFixed(1) }}x</span>
                      </div>
                      <!--  show angles-->
                      <q-checkbox
                        name="inverted_show_angles"
                        v-model="showAnglesValues"
                        @click="!showAnglesValues"
                        label="Показывать углы"
                        color="primary"
                        class=""
                      />
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
        </q-expansion-item>

        <!-- Добавление строительных работ и строительных материалов -->
        <q-expansion-item
          expand-separator
          icon="add_home_work"
          label="Добавление строительных работ и строительных материалов"
          header-class="text-indigo"
        >
          <q-card flat bordered class="q-pa-md q-mt-md">
            <q-tabs
              v-model="job_types_tabs"
              dense
              class="text-grey"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              narrow-indicator
            >
              <q-tab name="stretch_ceiling" label="Натяжной потолок" />
              <q-tab name="drywall" label="Гипсокартон" />
              <q-tab name="putty" label="Шпатлевка" />
            </q-tabs>
            <q-separator />

            <q-tab-panels v-model="job_types_tabs" animated>

              <q-tab-panel name="stretch_ceiling">
                <stretch-ceiling-calc :walls="walls"/>
              </q-tab-panel>

              <q-tab-panel name="drywall">
                <div class="text-h6">Гипсокартон</div>
              </q-tab-panel>

              <q-tab-panel name="putty">
                <div class="text-h6">Шпатлевка</div>
              </q-tab-panel>

            </q-tab-panels>
          </q-card>
        </q-expansion-item>

        <!-- Добавленные строительные работы -->
        <q-expansion-item
          expand-separator
          icon="construction"
          label="Добавленные строительные работы"
          header-class="text-purple-8"
        >
          <q-card>
            <q-card-section>
              <div>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem, eius reprehenderit eos corrupti
                commodi magni quaerat ex numquam, dolorum officiis modi facere maiores architecto suscipit iste
                eveniet doloribus ullam aliquid.
              </div>
              <div>{{ roomJobs.roomJobsSum }}</div>
              <div><pre>{{ roomJobs.roomJobs}}</pre></div>
            </q-card-section>
          </q-card>
        </q-expansion-item>

        <!-- Добавленные строительные материалы -->
        <q-expansion-item
          expand-separator
          icon="unarchive"
          label="Добавленные строительные материалы"
          header-class="text-purple-9"
        >
          <q-card>
            <q-card-section>
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem, eius reprehenderit eos corrupti
              commodi magni quaerat ex numquam, dolorum officiis modi facere maiores architecto suscipit iste
              eveniet doloribus ullam aliquid.
            </q-card-section>
          </q-card>
        </q-expansion-item>

      </q-list>
    </div>
  </q-page>
</template>

<script setup>
import {useRoute} from "vue-router"
import {computed, onMounted, ref} from "vue";
import {api} from "@/boot/axios.js";
import {Notify} from "quasar";
import {useRoomJobsStore} from "@/stores/roomJobs.js";
import StretchCeilingCalc from "@/components/JobTypesCalcs/StretchCeilingCalc.vue";

//const router = userRouter;
const route = useRoute();
const roomJobs = useRoomJobsStore();

const rrub = '&#8381;';

const room_chars_tabs = ref('walls');
const job_types_tabs = ref('stretch_ceiling');
const etalonWalls = ref(null);

const projectId = ref(null);
const roomId = ref(null);
const roomInfo = ref(null);
const wallsSaving = ref(false);

const roomHeight = ref(0);

const svgRef = ref(null)

const svgWidth = 350
const svgHeight = 230
const zoom = ref(2)
const panX = ref(-40)
const panY = ref(0)

const showAnglesValues = ref(false);
let isPanning = false
let startPoint = { x: 0, y: 0 }

const currentProjectRooms = computed(() => {
  return `/projects/${projectId.value}/rooms`;
});

const wallText = computed({
  get() {
    if (!walls.value || walls.value.length === 0) return ''
    return walls.value
      .map(wall => `${wall.length} ${wall.angle} ${wall.is_real ? 1 : 0}`) // Форматируем в строку
      .join('\n')
  },
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
    return response.data?.data;
  } catch (error) {
    throw error.response?.data || { message: 'Load roomWalls failed' };
  }
};

const saveWalls = async() => {
  if (!stats.value.is_closed){
    Notify.create({
      type: 'negative',
      message: 'Стены должны быть замкнуты!',
    });
    return;
  }

  wallsSaving.value = true;
  try {
    const response = await api.post(`/v1/roomWalls/${roomId.value}`,{
      walls: walls.value,
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

const walls = ref([])

const tmpWall = ref({
  length: 0,
  angle: 0,
  is_real: 1,
});

// Центральная точка для отображения (центр SVG)
const center = {x: 0, y: 100}

const addWall = () => {
  walls.value.push({
    length: parseFloat(tmpWall.value.length),
    angle: parseFloat(tmpWall.value.angle),
    is_real: parseFloat(tmpWall.value.is_real),
  })
  tmpWall.value.length = 0;
  tmpWall.value.angle = 0;
  tmpWall.value.is_real = 0;
}

const removeLastWall = () => {
  walls.value.pop()
}

const resetWalls = () => {
  walls.value = [...etalonWalls.value];
}

// получить углы
const getCornerAngle = (i) => {
  if (i <= 0 || i >= points.value.length - 1) return ''

  const A = points.value[i - 1]
  const B = points.value[i]
  const C = points.value[i + 1]

  const v1 = {x: A.x - B.x, y: A.y - B.y}
  const v2 = {x: C.x - B.x, y: C.y - B.y}

  const dot = v1.x * v2.x + v1.y * v2.y
  const len1 = Math.sqrt(v1.x * v1.x + v1.y * v1.y)
  const len2 = Math.sqrt(v2.x * v2.x + v2.y * v2.y)

  const angleRad = Math.acos(dot / (len1 * len2))
  const angleDeg = (angleRad * 180) / Math.PI

  return angleDeg.toFixed(1)
}

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

const ceilingSquare = computed( () => {
  return calculatePolygonSquare(walls.value);
});

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

/**
 * Подсчет площади многоугольника по формуле Гаусса.
 * @param segments
 * @returns {number}
 */
const calculatePolygonSquare = (segments) => {
  // Строим точки вершин, начиная с (0, 0)
  const points = [{ x: 0, y: 0 }];
  let x = 0;
  let y = 0;

  for (let val of segments) {
    const angleRad = val.angle * Math.PI / 180;
    x += val.length / 100 * Math.cos(angleRad);
    y += val.length / 100 * Math.sin(angleRad);
    points.push({ x, y });
  }

  // Формула шнурков (shoelace) для площади
  let area = 0;
  const n = points.length;
  for (let i = 0; i < n; i++) {
    const j = (i + 1) % n;
    area += points[i].x * points[j].y;
    area -= points[j].x * points[i].y;
  }

  return +(Math.abs(area) / 2).toFixed(2);
}

onMounted(async  () => {
  projectId.value = route.params.projectId;
  roomId.value = route.params.roomId;
  await getRoomInfo(projectId.value, roomId.value);
  roomHeight.value = roomInfo.value.height;

  etalonWalls.value = await getWallsInfo(roomId.value);
  walls.value = [...etalonWalls.value];

  const roomJobss = await roomJobs.loadRoomJobs(roomId.value);
  console.log(roomJobss);
});

</script>

<style scoped>
  .animated.shake{
    animation-duration: 1s !important;  /* Медленнее */
    animation-iteration-count: 1 !important;  /* 3 раза */
  }
</style>
