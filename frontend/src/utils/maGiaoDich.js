const NAP_PREFIX = 'NAP'
const PAY_PREFIX = 'PAY'
const SUFFIX = 'ECOIN'
const TOKEN_LENGTH = 8
const ALNUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'

function randomFromAlphabet(alphabet, length) {
  const result = []
  const maxUnbiased = 256 - (256 % alphabet.length)
  while (result.length < length) {
    const bytes = crypto.getRandomValues(new Uint8Array(length * 2))
    for (const byte of bytes) {
      if (byte >= maxUnbiased) continue
      result.push(alphabet[byte % alphabet.length])
      if (result.length === length) break
    }
  }
  return result.join('')
}

/** NAP + 8 ký tự A-Z0-9 + ECOIN */
export function taoMaNap() {
  return `${NAP_PREFIX}${randomFromAlphabet(ALNUM, TOKEN_LENGTH)}${SUFFIX}`
}

/** PAY + 8 chữ số + ECOIN */
export function taoMaThanhToan() {
  return `${PAY_PREFIX}${randomFromAlphabet('0123456789', TOKEN_LENGTH)}${SUFFIX}`
}
