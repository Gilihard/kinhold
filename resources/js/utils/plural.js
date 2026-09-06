// Russian pluralization helper. Russian has three grammatical forms
// depending on the number, e.g. 1 балл, 2 балла, 5 баллов.
export function pluralRu(n, one, few, many) {
  const abs = Math.abs(Math.trunc(n))
  const mod10 = abs % 10
  const mod100 = abs % 100
  if (mod10 === 1 && mod100 !== 11) return one
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few
  return many
}

export function formatPts(n) {
  return `${n} ${pluralRu(n, 'балл', 'балла', 'баллов')}`
}

export function ptsWord(n) {
  return pluralRu(n, 'балл', 'балла', 'баллов')
}
