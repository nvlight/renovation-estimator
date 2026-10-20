import { computed, unref } from 'vue'
import { Notify } from 'quasar'
import { useMaterialsStore } from '@/stores/materials.js'
import { useRoomMaterialsStore } from '@/stores/roomMaterials.js'
import { getMaterialTotalSum } from './drywallMaterials.js'

export const useDrywallMaterials = ({ roomId, materialAmounts, allAddedMessage }) => {
  const materialsStore = useMaterialsStore()
  const roomMaterialsStore = useRoomMaterialsStore()

  const rows = computed(() => {
    return Object.entries(unref(materialAmounts))
      .map(([id, amount]) => {
        const normalizedAmount = Number(amount)
        const material = materialsStore.items.find((candidate) => candidate.id === Number(id))

        if (!material || !Number.isFinite(normalizedAmount) || normalizedAmount < 1) {
          return null
        }

        return {
          id: material.id,
          title: material.title,
          amount: normalizedAmount,
          price: material.price,
          sum: getMaterialTotalSum(material.title, material.price, normalizedAmount),
        }
      })
      .filter(Boolean)
  })

  const allMaterialsSum = computed(() => {
    return rows.value.reduce((sum, row) => sum + row.sum, 0)
  })

  const notifyAddError = () => {
    Notify.create({
      type: 'negative',
      message: 'Не удалось добавить строительные материалы.',
    })
  }

  const isSuccessful = (response) => response?.status >= 200 && response.status < 300

  const addMaterial = async (row) => {
    const normalizedRoomId = unref(roomId)
    const response = await roomMaterialsStore.addItem(normalizedRoomId, {
      room_id: normalizedRoomId,
      material_id: row.id,
      amount: row.amount,
      sum: row.sum,
    })

    if (!isSuccessful(response)) {
      notifyAddError()
      return
    }

    Notify.create({
      type: 'positive',
      message: 'Строительный материал добавлен!',
    })
  }

  const addAllMaterials = async () => {
    const materials = rows.value.map((row) => ({
      material_id: row.id,
      amount: row.amount,
      sum: row.sum,
    }))

    if (materials.length === 0) {
      return
    }

    const response = await roomMaterialsStore.addItems(unref(roomId), { materials })

    if (!isSuccessful(response)) {
      notifyAddError()
      return
    }

    Notify.create({
      type: 'positive',
      message: allAddedMessage,
    })
  }

  return {
    materialsStore,
    rows,
    allMaterialsSum,
    addMaterial,
    addAllMaterials,
  }
}
