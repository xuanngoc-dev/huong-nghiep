/**
 * Cấu hình hiệu ứng chúc mừng + pháo hoa (bước Ngành phù hợp).
 * Đơn vị thời gian: mili giây (ms). Đổi số tại đây để tuỳ chỉnh.
 */
export const quizFieldsCelebration = {
  eyebrow: 'Hoàn thành khảo sát',

  /** Thời gian tổng */
  fireworksDurationMs: 4800, // pháo hoa chạy bao lâu
  messageVisibleMs: 4200, // chữ hiện bao lâu trước khi ẩn

  /** Chữ tiêu đề đỏ */
  title: {
    text: 'Chúc mừng bạn!',
    enterDurationMs: 650, // thời gian 1 chữ bung vào
    startDelayMs: 220, // chờ trước chữ đầu
    staggerMs: 75, // khoảng cách giữa các chữ
  },

  /** Dòng phụ màu cam */
  subtitle: {
    text: 'Cảm ơn bạn đã tham gia — khám phá ngành phù hợp bên dưới nhé!',
    enterDurationMs: 550, // fade-in từng chữ
    startDelayMs: 1150, // chờ trước chữ đầu dòng phụ
    staggerMs: 48, // khoảng cách fade-in giữa các chữ
    waveDurationMs: 1450, // chu kỳ sóng lên/xuống
    waveStartDelayMs: 1850, // chờ trước khi bắt đầu sóng
    waveStaggerMs: 60, // lệch pha sóng giữa các chữ
  },

  /** Overlay vào / ra */
  overlay: {
    enterOpacityMs: 350,
    enterTransformMs: 450,
    leaveMs: 550,
  },
}
