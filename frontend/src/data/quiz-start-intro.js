export const quizStartIntro = {
  title: 'Giới Thiệu Trắc Nghiệm Hướng Nghiệp',
  paragraphs: [
    'Trắc nghiệm này giúp bạn hiểu rõ hơn về bản thân, sở thích và xu hướng nghề nghiệp phù hợp.',
    'Không có đáp án đúng hay sai — điều quan trọng nhất là bạn trung thực với chính mình.',
  ],
  sections: [
    {
      id: 'before',
      icon: '✅',
      title: 'Trước khi bắt đầu, bạn cần:',
      items: [
        'Tham gia một cách hoàn toàn tự nguyện.',
        'Dành khoảng 5 phút để thư giãn và tập trung.',
        'Hiểu rằng trắc nghiệm này nhằm giúp bạn hiểu mình hơn, không phải để tạo hình ảnh “hoàn hảo” cho người khác.',
        'Trung thực với bản thân, không khen hoặc chê quá mức.',
        'Hoàn thành bài trong khoảng 20 phút để kết quả phản ánh đúng nhất.',
      ],
    },
    {
      id: 'during',
      icon: '📝',
      title: 'Trong quá trình làm bài, bạn cần:',
      items: [
        'Trả lời nhanh, tự nhiên, theo cảm nhận đầu tiên — đừng suy nghĩ quá nhiều.',
        'Với mỗi mô tả, hãy chọn mức độ phù hợp với bạn theo 5 mức:',
      ],
      showScale: true,
    },
    {
      id: 'result',
      icon: '📈',
      title: 'Cách đọc kết quả:',
      lead: 'Mỗi nhóm ngành có 10 câu hỏi. Tổng điểm của nhóm sẽ cho bạn biết mức độ phù hợp:',
      scoreGuides: [
        {
          range: '35-40 điểm',
          text: 'Bạn có mức độ phù hợp cao với nhóm ngành đó.',
        },
        {
          range: '< 35 điểm',
          text: 'Bạn chưa phù hợp với nhóm ngành đó và cần cân nhắc kỹ lưỡng, kết hợp với nhiều yếu tố khác.',
        },
      ],
    },
    {
      id: 'note',
      icon: '🔔',
      title: 'Lưu ý:',
      body: 'Kết quả trắc nghiệm chỉ mang tính tham khảo, hỗ trợ định hướng ban đầu và không thay thế tư vấn hướng nghiệp chuyên sâu từ chuyên gia.',
    },
  ],
  scale: [
    { key: 'A', points: '5 điểm', color: '#e74c3c' },
    { key: 'B', points: '4 điểm', color: '#f1c40f' },
    { key: 'C', points: '3 điểm', color: '#2ecc71' },
    { key: 'D', points: '2 điểm', color: '#1abc9c' },
    { key: 'E', points: '1 điểm', color: '#3498db' },
  ],
}
