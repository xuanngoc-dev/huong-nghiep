/** Nội dung trang chủ — tách data khỏi UI để dễ bảo trì / i18n sau này */

export const heroContent = {
  brand: 'Hướng Nghiệp',
  eyebrow: 'Định hướng tương lai ngay hôm nay',
  description:
    'Trắc nghiệm năng lực, thư viện ngành nghề và gợi ý chọn trường — đồng hành cùng bạn trên hành trình định hướng nghề nghiệp, giúp bạn hiểu rõ bản thân và chọn hướng đi phù hợp.',
  descriptionShort:
    'Trắc nghiệm năng lực, thư viện ngành nghề và gợi ý chọn trường phù hợp với bạn.',
  primaryCta: { label: 'Bắt đầu ngay', to: '/assessments' },
  secondaryCta: { label: 'Tìm hiểu thêm', to: '/careers' },
  banners: [
    {
      title: 'Khám phá sự nghiệp phù hợp với bạn',
      image: '/images/banners/banner-1.jpg',
      alt: 'Nhóm sinh viên thảo luận và học tập cùng nhau',
    },
    {
      title: 'Trắc nghiệm năng lực — hiểu rõ bản thân',
      image: '/images/banners/banner-2.jpg',
      alt: 'Sinh viên hợp tác làm việc nhóm',
    },
    {
      title: 'Chọn ngành — chọn trường tự tin hơn',
      image: '/images/banners/banner-3.jpg',
      alt: 'Học sinh tập trung ôn luyện và định hướng',
    },
  ],
  stats: [
    { value: 10, suffix: 'K+', label: 'Người dùng tin tưởng', shortLabel: 'Người dùng' },
    { value: 98, suffix: '%', label: 'Độ hài lòng', shortLabel: 'Hài lòng' },
    { value: 500, suffix: '+', label: 'Ngành nghề khám phá', shortLabel: 'Ngành nghề' },
  ],
}

export const audiencesContent = {
  eyebrow: 'Dành cho mọi lứa tuổi',
  title: 'Ai nên tham gia chương trình?',
  description: 'Khám phá ngành nghề phù hợp với từng giai đoạn cuộc đời',
  items: [
    {
      id: 'thcs',
      icon: 'School',
      title: 'Học sinh THCS',
      description:
        'Giúp học sinh định hướng nghề nghiệp từ sớm, tự tin chọn trường, chọn ngành',
      to: '/assessments',
    },
    {
      id: 'thpt',
      icon: 'Reading',
      title: 'Học sinh THPT',
      description:
        'Hỗ trợ chọn ngành, chọn trường đại học phù hợp với năng lực, sở thích',
      to: '/careers',
    },
    {
      id: 'sv',
      icon: 'Medal',
      title: 'Sinh viên',
      description:
        'Định hướng nghề nghiệp, phát triển bản thân, chuẩn bị cho thị trường lao động',
      to: '/careers',
    },
    {
      id: 'work',
      icon: 'Briefcase',
      title: 'Người đi làm',
      description:
        'Khám phá năng lực, sở thích để phát triển sự nghiệp, chuyển đổi công việc',
      to: '/assessments',
    },
  ],
}

export const servicesContent = {
  eyebrow: 'Tính năng nổi bật',
  title: 'Công cụ hỗ trợ định hướng',
  description: 'Ba trụ cột giúp bạn hiểu bản thân và chọn đúng hướng đi',
  items: [
    {
      id: 'assessments',
      icon: 'Document',
      title: 'Trắc nghiệm hướng nghiệp',
      description: 'Bài test khoa học giúp nhận diện tính cách, năng lực và sở thích nghề nghiệp.',
      to: '/assessments',
      cta: 'Làm bài ngay',
    },
    {
      id: 'careers',
      icon: 'Collection',
      title: 'Thư viện ngành nghề',
      description: 'Khám phá hàng trăm ngành nghề với mô tả chi tiết, yêu cầu và triển vọng.',
      to: '/careers',
      cta: 'Xem ngành nghề',
    },
    {
      id: 'articles',
      icon: 'Notebook',
      title: 'Tin tức & bài viết',
      description: 'Cập nhật kiến thức tuyển sinh, xu hướng nghề nghiệp và lời khuyên thực tiễn.',
      to: '/articles',
      cta: 'Đọc bài viết',
    },
  ],
}

export const admissionContent = {
  eyebrow: 'Mùa tuyển sinh',
  title: 'Cẩm nang tuyển sinh & tra cứu phương thức xét tuyển',
  description:
    'Điểm chuẩn, chỉ tiêu, đề án tuyển sinh và gợi ý ngành — trường dành cho học sinh lớp 9, lớp 12 và phụ huynh.',
  primaryCta: { label: 'Xem nghề nghiệp', to: '/careers' },
  secondaryCta: { label: 'Làm trắc nghiệm', to: '/assessments' },
}
