export const materialColumns = [
  { name: 'actions', label: '#', field: 'actions', sortable: false, align: 'left' },
  { name: 'id', label: 'id', field: 'id', sortable: true, align: 'left' },
  { name: 'title', label: 'Материал', field: 'title', sortable: true, align: 'left' },
  { name: 'amount', label: 'Количество', field: 'amount', sortable: false, align: 'right' },
  { name: 'price', label: 'Цена', field: 'price', sortable: false, align: 'right' },
  { name: 'sum', label: 'Сумма', field: 'sum', sortable: false, align: 'right' },
]

export const positiveNumber = (value) => {
  const number = Number(value)

  return Number.isFinite(number) && number > 0 ? number : 0
}

export const amountByStep = (length, step) => {
  const normalizedLength = positiveNumber(length)
  const normalizedStep = positiveNumber(step)

  if (!normalizedLength || !normalizedStep) {
    return 0
  }

  return Math.ceil(normalizedLength / normalizedStep)
}

export const mergeMaterialAmounts = (...groups) => {
  return groups.reduce((result, group) => {
    Object.entries(group).forEach(([id, amount]) => {
      const normalizedAmount = Number(amount)

      if (Number.isFinite(normalizedAmount)) {
        result[id] = (result[id] || 0) + normalizedAmount
      }
    })

    return result
  }, {})
}

export const getMaterialTotalSum = (title, price, amount) => {
  const packageMatch = title.match(/(\d+(?:[.,]\d+)?)\s*шт/i)
  const quantityPerPackage = packageMatch ? Number.parseFloat(packageMatch[1].replace(',', '.')) : 1

  if (!Number.isFinite(quantityPerPackage) || quantityPerPackage <= 0) {
    return Math.ceil(amount * price)
  }

  return Math.ceil((amount / quantityPerPackage) * price)
}
