export function useSafeConsole() {
  if (import.meta.env.MODE === 'production') {
    const suppressed = ['log', 'info', 'warn', 'debug', 'trace']

    suppressed.forEach((method) => {
      console[method] = () => {}
    })
    console.info('🚫 Konsol logları production ortamında devre dışı')
  } else {
    console.info('✅ Konsol logları development ortamında aktif')
  }
}
