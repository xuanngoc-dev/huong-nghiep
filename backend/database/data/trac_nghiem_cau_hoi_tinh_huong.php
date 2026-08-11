<?php

/**
 * Ngân hàng câu hỏi tình huống: mỗi nhóm ngành × mỗi loại có 10 câu,
 * mỗi câu 5 đáp án tình huống với điểm 1–5 (5 = khớp nhóm ngành nhất).
 *
 * @return array<string, array<string, list<array{cau_hoi: string, dap_an: list<array{noi_dung: string, diem: int}>}>>>
 */
$questionsByLoai = [
    'STDM' => [
        'Bạn thường làm gì khi có thời gian rảnh?',
        'Hoạt động nào khiến bạn cảm thấy hứng thú nhất?',
        'Bạn vui nhất khi hoàn thành việc gì?',
        'Nếu được chọn một câu lạc bộ, bạn nghiêng về đâu?',
        'Loại nội dung bạn xem/đọc nhiều nhất là gì?',
        'Bạn muốn thử trải nghiệm nào dưới đây?',
        'Điều gì khiến bạn muốn theo đuổi lâu dài?',
        'Chủ đề nào bạn sẵn sàng tìm hiểu sâu?',
        'Cuối tuần lý tưởng của bạn thường diễn ra thế nào?',
        'Hình ảnh công việc nào khiến bạn thấy “đúng chỗ” nhất?',
    ],
    'KNKN' => [
        'Khi gặp việc mới/lạ, bạn thường xử lý thế nào?',
        'Bạn tự đánh giá thế mạnh nào gần với mình nhất?',
        'Trong dự án nhóm, bạn thường nhận phần việc nào?',
        'Cách học kỹ năng mới nào hiệu quả với bạn?',
        'Bạn tự tin nhất khi được giao nhiệm vụ nào?',
        'Khi gặp trở ngại, phản xạ đầu tiên của bạn là gì?',
        'Công cụ/kỹ năng nào bạn muốn thành thạo nhất?',
        'Bạn xử lý thông tin phức tạp theo cách nào?',
        'Người khác thường nhờ bạn giúp việc gì?',
        'Câu nào giống bạn nhất khi nói về năng lực?',
    ],
    'MTLV' => [
        'Bạn muốn môi trường làm việc như thế nào?',
        'Bạn cảm thấy thoải mái hơn khi làm việc ở đâu?',
        'Điều kiện làm việc nào quan trọng nhất với bạn?',
        'Đồng nghiệp lý tưởng quanh bạn thuộc kiểu nào?',
        'Một ngày làm việc lý tưởng của bạn có yếu tố gì?',
        'Bạn sẵn sàng với môi trường biến động đến mức nào?',
        'Không gian nào bạn muốn gắn bó lâu dài?',
        'Áp lực công việc kiểu nào bạn chịu tốt hơn?',
        'Bạn cần gì nhất ở nơi làm việc để phát huy?',
        'Môi trường nào khiến bạn thấy đúng sở trường?',
    ],
    'PCLV' => [
        'Bạn thường tiếp cận nhiệm vụ mới như thế nào?',
        'Khi làm việc nhóm, phong cách của bạn nghiêng về đâu?',
        'Bạn ưu tiên điều gì khi giải quyết vấn đề?',
        'Bạn thích kế hoạch công việc dạng nào?',
        'Khi bị tắc giữa chừng, bạn thường làm gì?',
        'Bạn đánh giá một ngày làm việc “đáng” dựa trên gì?',
        'Bạn thích ra quyết định theo cách nào?',
        'Nhịp độ làm việc nào hợp với bạn?',
        'Bạn muốn được đánh giá công việc chủ yếu qua điều gì?',
        'Câu mô tả nào gần với phong cách làm việc của bạn?',
    ],
];

/**
 * Matchers để map tên nhóm ngành trong DB → key ngân hàng.
 *
 * @var array<string, list<string>>
 */
$nhomMatchers = [
    'ky_thuat' => ['kỹ thuật', 'công nghệ', 'engineering', 'technology'],
    'khoa_hoc' => ['khoa học', 'nghiên cứu', 'science', 'research'],
    'giao_duc' => ['giáo dục', 'xã hội', 'education', 'social'],
    'kinh_doanh' => ['kinh doanh', 'quản lý', 'quản trị', 'business'],
    'nghe_thuat' => ['nghệ thuật', 'sáng tạo', 'arts', 'creativity'],
    'quan_doi' => ['quân đội', 'công an', 'hàng không', 'military', 'police', 'aviation'],
];

/**
 * Bộ đáp án chuẩn theo 6 định hướng (thứ tự cố định):
 * 0 xã hội, 1 nghệ thuật, 2 kinh doanh, 3 khoa học, 4 kỹ thuật, 5 kỷ luật/quân sự
 * Mỗi nhóm sẽ map điểm 5 vào đúng “slot” của mình, các slot còn lại nhận 1–4.
 */
$orientationSlots = [
    'giao_duc' => 0,
    'nghe_thuat' => 1,
    'kinh_doanh' => 2,
    'khoa_hoc' => 3,
    'ky_thuat' => 4,
    'quan_doi' => 5,
];
/**
 * 6 phương án tình huống cho mỗi câu (theo 6 định hướng).
 * Sau đó với mỗi nhóm, chọn 5 phương án và gán điểm sao cho phương án của nhóm = 5.
 *
 * @var array<string, list<list<string>>>
 */
$sixWayAnswers = [
    'STDM' => [
        [
            'Tôi thích trò chuyện, lắng nghe và giúp người khác giải quyết chuyện cá nhân.',
            'Tôi thích vẽ, nghe nhạc, làm đồ handmade hoặc thử ý tưởng sáng tạo.',
            'Tôi thích đọc tin kinh tế, lập kế hoạch hoặc nghĩ ý tưởng kinh doanh nhỏ.',
            'Tôi thích đọc bài khoa học, xem thí nghiệm hoặc tìm hiểu “vì sao”.',
            'Tôi thích sửa đồ hỏng, lắp ráp máy móc hoặc vọc thiết bị kỹ thuật.',
            'Tôi thích rèn kỷ luật bản thân, tập luyện thể lực hoặc hoạt động đội nhóm có quy tắc rõ.',
        ],
        [
            'Tổ chức buổi chia sẻ, hướng dẫn hoặc hỗ trợ cộng đồng.',
            'Thiết kế poster, chỉnh ảnh, làm video hoặc trang trí không gian.',
            'Thuyết trình ý tưởng, thương lượng hoặc dẫn dắt một nhóm nhỏ.',
            'Phân tích số liệu, giải bài toán hoặc kiểm chứng một giả thuyết.',
            'Chế tạo mô hình, lắp mạch hoặc bảo trì thiết bị.',
            'Tham gia hoạt động đội ngũ, huấn luyện kỹ năng hoặc mô phỏng tình huống khẩn cấp.',
        ],
        [
            'Giúp ai đó vượt qua khó khăn hoặc học được điều mới.',
            'Tạo ra sản phẩm đẹp, ấn tượng về thẩm mỹ.',
            'Chốt được thỏa thuận hoặc đạt mục tiêu kinh doanh/kế hoạch.',
            'Tìm ra lời giải hoặc quy luật cho một vấn đề phức tạp.',
            'Làm ra hoặc sửa được thiết bị/sản phẩm vận hành tốt.',
            'Hoàn thành nhiệm vụ đúng quy trình, đúng thời hạn trong điều kiện áp lực.',
        ],
        [
            'Câu lạc bộ tình nguyện, mentorship hoặc kỹ năng mềm.',
            'Câu lạc bộ nghệ thuật, nhiếp ảnh, thiết kế hoặc truyền thông.',
            'Câu lạc bộ khởi nghiệp, tranh biện hoặc quản trị dự án.',
            'Câu lạc bộ khoa học, nghiên cứu hoặc lập trình thuật toán.',
            'Câu lạc bộ robot, cơ khí, điện tử hoặc chế tạo.',
            'Câu lạc bộ điều lệnh, cứu hộ, hàng không mô hình hoặc thể thao đối kháng có kỷ luật.',
        ],
        [
            'Podcast/talk show về giáo dục, tâm lý hoặc đời sống.',
            'Video nghệ thuật, thiết kế, phim ảnh hoặc sáng tạo.',
            'Nội dung kinh doanh, tài chính cá nhân hoặc lãnh đạo.',
            'Kênh khoa học, khám phá hoặc phân tích chuyên sâu.',
            'Video tháo máy, sửa chữa, chế tạo hoặc công nghệ phần cứng.',
            'Nội dung về kỷ luật, kỹ năng sinh tồn, an ninh hoặc hàng không.',
        ],
        [
            'Làm trợ giảng hoặc tham gia chương trình hỗ trợ cộng đồng.',
            'Tham gia workshop vẽ, thiết kế hoặc sản xuất nội dung sáng tạo.',
            'Tham gia cuộc thi pitching ý tưởng kinh doanh.',
            'Tham gia đề tài nghiên cứu hoặc hackathon giải bài toán.',
            'Tham gia cuộc thi robot, lắp ráp thiết bị hoặc thực tập xưởng.',
            'Tham gia trại huấn luyện, diễn tập tình huống hoặc hoạt động đội ngũ có kỷ luật.',
        ],
        [
            'Được đồng hành và tạo ảnh hưởng tích cực đến người khác.',
            'Được tự do thể hiện cá tính và sáng tạo không giới hạn.',
            'Được dẫn dắt dự án, tạo ra giá trị kinh tế rõ ràng.',
            'Được khám phá tri thức và đóng góp hiểu biết mới.',
            'Được làm việc với máy móc, hệ thống kỹ thuật và tạo sản phẩm thực.',
            'Được phục vụ tập thể, bảo vệ an toàn và hoàn thành sứ mệnh chung.',
        ],
        [
            'Tâm lý con người, giáo dục hoặc công tác xã hội.',
            'Nghệ thuật, thiết kế, truyền thông sáng tạo.',
            'Kinh doanh, marketing hoặc quản trị.',
            'Khoa học dữ liệu, nghiên cứu hoặc phân tích vấn đề.',
            'Cơ khí, điện – điện tử, lập trình hệ thống hoặc công nghệ kỹ thuật.',
            'An ninh, hàng không, cứu hộ hoặc quản lý tình huống khẩn cấp.',
        ],
        [
            'Gặp gỡ bạn bè, tình nguyện hoặc hoạt động cộng đồng.',
            'Đi triển lãm, làm sản phẩm thủ công nghệ thuật.',
            'Networking, workshop kinh doanh hoặc lập kế hoạch cá nhân.',
            'Đọc sách khoa học hoặc thử nghiệm ý tưởng nghiên cứu nhỏ.',
            'Làm dự án DIY, sửa xe/đồ điện hoặc thực hành kỹ thuật.',
            'Tập luyện thể lực, diễn tập đội nhóm hoặc rèn kỹ năng ứng phó.',
        ],
        [
            'Lớp học, trung tâm tư vấn hoặc không gian hỗ trợ cộng đồng.',
            'Studio thiết kế, triển lãm hoặc không gian sáng tạo.',
            'Phòng họp chiến lược hoặc môi trường kinh doanh năng động.',
            'Phòng thí nghiệm với biểu đồ, mẫu vật và thiết bị đo.',
            'Nhà xưởng, lab kỹ thuật hoặc khu vực vận hành thiết bị.',
            'Đơn vị có kỷ luật rõ, đồng phục/quy trình chuẩn hoặc môi trường hàng không – an ninh.',
        ],
    ],
    'KNKN' => [
        [
            'Hỏi người khác cảm nhận rồi điều chỉnh cách làm cho dễ chịu.',
            'Thử theo cảm hứng, chấp nhận làm lại nếu chưa đẹp.',
            'Xác định mục tiêu lợi ích trước, rồi chọn cách nhanh nhất.',
            'Phân tách vấn đề, đặt giả thuyết và tìm bằng chứng.',
            'Tự mày mò thiết bị/công cụ, thử cấu hình đến khi hiểu.',
            'Bám quy trình, kiểm soát rủi ro và hoàn thành đúng mốc.',
        ],
        [
            'Lắng nghe, đồng cảm và hỗ trợ người khác.',
            'Cảm thẩm mỹ, ý tưởng hình ảnh và sự sáng tạo.',
            'Thuyết phục, đàm phán và tổ chức nhóm.',
            'Phân tích logic, làm việc với số liệu và suy luận.',
            'Khéo tay, sử dụng công cụ và xử lý kỹ thuật thực tế.',
            'Giữ bình tĩnh dưới áp lực, tuân thủ quy tắc và phối hợp đội ngũ.',
        ],
        [
            'Kết nối thành viên, hỗ trợ tinh thần và chia sẻ công việc.',
            'Thiết kế slide, hình ảnh hoặc phần trình bày đẹp mắt.',
            'Điều phối tiến độ, thuyết trình và đại diện nhóm.',
            'Nghiên cứu tài liệu, phân tích số liệu và đề xuất phương án.',
            'Làm phần kỹ thuật: lắp ráp, lập trình thiết bị, kiểm tra vận hành.',
            'Phân công theo vai trò rõ, giám sát kỷ luật và đảm bảo an toàn.',
        ],
        [
            'Học cùng người khác, được kèm cặp và phản hồi liên tục.',
            'Học qua cảm hứng, thử nghiệm tự do theo gu cá nhân.',
            'Học theo mục tiêu rõ ràng, áp dụng ngay vào kết quả.',
            'Đọc lý thuyết kỹ, làm bài tập và kiểm chứng kết quả.',
            'Học bằng thực hành tay: tháo lắp, sửa lỗi trực tiếp.',
            'Học qua huấn luyện lặp lại, mô phỏng tình huống và đánh giá kỷ luật.',
        ],
        [
            'Hỗ trợ thành viên mới hòa nhập và giải đáp thắc mắc.',
            'Làm mới giao diện, bao bì hoặc ý tưởng trình bày sáng tạo.',
            'Đàm phán với đối tác và chốt phương án triển khai.',
            'Phân tích nguyên nhân gốc rễ của một sự cố phức tạp.',
            'Lắp đặt, hiệu chỉnh hoặc bảo trì một hệ thống kỹ thuật.',
            'Điều phối đội ngũ trong tình huống gấp, bảo đảm an toàn và đúng quy trình.',
        ],
        [
            'Tìm người hỗ trợ tinh thần rồi mới quay lại.',
            'Đổi hướng sang ý tưởng khác thú vị hơn.',
            'Đánh giá lại mục tiêu và phân công người khác làm phần đó.',
            'Rà lại giả thuyết, phương pháp và chạy phân tích bổ sung.',
            'Thử – sai có hệ thống trên thiết bị đến khi khoanh được lỗi.',
            'Báo cáo theo quy trình, giữ vị trí và xử lý theo checklist an toàn.',
        ],
        [
            'Công cụ hỗ trợ giao tiếp, giảng dạy hoặc chăm sóc người dùng.',
            'Phần mềm thiết kế đồ họa hoặc dựng video.',
            'Công cụ quản trị dự án, CRM hoặc phân tích thị trường.',
            'Python/R, SQL, SPSS hoặc công cụ thống kê – mô phỏng.',
            'Dụng cụ cầm tay, thiết bị đo, máy móc hoặc phần mềm kỹ thuật.',
            'Công cụ huấn luyện, mô phỏng tình huống hoặc hệ thống giám sát an ninh/hàng không.',
        ],
        [
            'Tóm tắt theo cảm nhận chung của mọi người.',
            'Chuyển thành hình ảnh, câu chuyện dễ nhớ.',
            'Rút ra hành động và ưu tiên mang lại lợi ích nhanh.',
            'Phân loại biến, tìm mối liên hệ và kiểm tra tính nhất quán.',
            'Biến thành quy trình thao tác cụ thể để làm được ngay trên thiết bị.',
            'Rút thành mệnh lệnh/checklist ngắn, rõ trách nhiệm từng người.',
        ],
        [
            'Nghe tư vấn chuyện cá nhân hoặc hòa giải mâu thuẫn.',
            'Góp ý thẩm mỹ, chỉnh sửa hình ảnh hoặc nội dung.',
            'Lập kế hoạch, thuyết trình hoặc đàm phán giúp.',
            'Giải thích khái niệm khó hoặc phân tích số liệu.',
            'Sửa đồ, lắp đặt thiết bị hoặc xử lý sự cố kỹ thuật.',
            'Giữ trật tự, hướng dẫn quy tắc an toàn hoặc hỗ trợ tình huống khẩn.',
        ],
        [
            'Tôi mạnh về đồng hành và phát triển con người.',
            'Tôi mạnh về sáng tạo và thể hiện ý tưởng.',
            'Tôi mạnh về định hướng mục tiêu và kết quả.',
            'Tôi mạnh về tư duy phân tích và khám phá tri thức.',
            'Tôi mạnh về thực hành kỹ thuật và vận hành hệ thống.',
            'Tôi mạnh về kỷ luật, chịu áp lực và phối hợp đội ngũ trong tình huống khó.',
        ],
    ],
    'MTLV' => [
        [
            'Không gian ấm áp, nhiều tương tác và hỗ trợ lẫn nhau.',
            'Không gian tự do, thẩm mỹ cao, khuyến khích sáng tạo.',
            'Môi trường năng động, cạnh tranh và hướng đến mục tiêu.',
            'Phòng lab/nghiên cứu yên tĩnh, đề cao phân tích và chính xác.',
            'Xưởng, hiện trường hoặc không gian kỹ thuật có máy móc, công cụ.',
            'Môi trường có kỷ luật rõ, quy trình chuẩn và ý thức trách nhiệm cao.',
        ],
        [
            'Lớp học, trung tâm tư vấn hoặc không gian cộng đồng.',
            'Studio thiết kế, không gian sáng tạo hoặc làm việc linh hoạt.',
            'Văn phòng kinh doanh, phòng họp hoặc sàn giao dịch ý tưởng.',
            'Phòng thí nghiệm, thư viện chuyên ngành hoặc góc nghiên cứu.',
            'Nhà xưởng, lab kỹ thuật, công trường hoặc khu vực vận hành thiết bị.',
            'Đơn vị có trực ca, diễn tập định kỳ hoặc môi trường hàng không – an ninh.',
        ],
        [
            'Được tiếp xúc nhiều với người, có ý nghĩa xã hội.',
            'Được tự do thể hiện ý tưởng, không bị gò bó quy trình cứng.',
            'Có cơ hội thăng tiến, thưởng rõ ràng theo kết quả.',
            'Được đào sâu chuyên môn và cập nhật tri thức mới.',
            'Được tiếp xúc thiết bị thực tế và nhìn thấy sản phẩm vận hành.',
            'Được làm việc trong khung quy tắc rõ, đề cao trách nhiệm và an toàn.',
        ],
        [
            'Người ấm áp, dễ chia sẻ và hỗ trợ lẫn nhau.',
            'Người giàu ý tưởng, cá tính và tư duy thẩm mỹ.',
            'Người quyết đoán, định hướng mục tiêu và kết quả.',
            'Người logic, thích tranh luận chuyên môn dựa trên bằng chứng.',
            'Người thực tế, khéo tay và giỏi xử lý kỹ thuật tại chỗ.',
            'Người đúng giờ, kỷ luật, sẵn sàng phối hợp theo phân công.',
        ],
        [
            'Nhiều buổi trao đổi, mentoring hoặc hỗ trợ người học/khách hàng.',
            'Thời gian tự do sáng tạo sản phẩm hình ảnh/nội dung.',
            'Các cuộc họp chiến lược, đàm phán và chốt quyết định.',
            'Thời gian tập trung phân tích, thí nghiệm hoặc viết báo cáo.',
            'Thời gian thực hành tại xưởng/lab, kiểm tra và tối ưu thiết bị.',
            'Ca trực, buổi huấn luyện hoặc phiên làm việc theo quy trình nghiêm ngặt.',
        ],
        [
            'Ưu tiên môi trường ổn định về cảm xúc và quan hệ.',
            'Chấp nhận thay đổi nếu phục vụ ý tưởng sáng tạo mới.',
            'Thích nghi nhanh nếu mang lại cơ hội và kết quả rõ.',
            'Ổn với thay đổi nếu có dữ liệu và phương pháp kiểm soát.',
            'Sẵn sàng với thay đổi thiết bị, quy trình và sự cố kỹ thuật.',
            'Sẵn sàng với tình huống đột xuất, trực đêm hoặc điều kiện khắc nghiệt có kỷ luật.',
        ],
        [
            'Trường học, tổ chức xã hội hoặc trung tâm hỗ trợ cộng đồng.',
            'Agency sáng tạo, studio nghệ thuật hoặc công ty truyền thông.',
            'Doanh nghiệp thương mại, startup hoặc tổ chức quản trị.',
            'Viện nghiên cứu, trường đại học hoặc công ty công nghệ sâu.',
            'Nhà máy, công ty kỹ thuật hoặc trung tâm vận hành hệ thống.',
            'Đơn vị quân đội/công an/hàng không hoặc tổ chức có cơ cấu chỉ huy rõ.',
        ],
        [
            'Áp lực cảm xúc khi làm việc với nhiều hoàn cảnh khác nhau.',
            'Áp lực phải liên tục đổi mới ý tưởng và gu thẩm mỹ.',
            'Áp lực doanh số, tiến độ thương vụ và cạnh tranh thị trường.',
            'Áp lực độ chính xác và thời hạn nghiên cứu/phân tích.',
            'Áp lực sự cố kỹ thuật cần xử lý nhanh, đúng quy trình.',
            'Áp lực tình huống khẩn, trách nhiệm an toàn và kỷ luật tập thể.',
        ],
        [
            'Văn hóa hỗ trợ và ý nghĩa xã hội rõ ràng.',
            'Tự do thể hiện và không khí sáng tạo.',
            'Cơ hội thăng tiến và thưởng theo kết quả.',
            'Nguồn dữ liệu, tài liệu chuyên môn và thời gian đào sâu.',
            'Thiết bị tốt, quy trình rõ và cơ hội thực hành kỹ thuật.',
            'Kỷ luật rõ ràng, phân cấp trách nhiệm và tinh thần đồng đội.',
        ],
        [
            'Nơi mình được giúp người khác mỗi ngày.',
            'Nơi mình được sáng tạo không ngừng.',
            'Nơi mình được dẫn dắt và tạo ảnh hưởng.',
            'Nơi mình được đặt câu hỏi và tìm ra câu trả lời.',
            'Nơi mình được làm việc với máy móc và hệ thống kỹ thuật.',
            'Nơi mình được phục vụ sứ mệnh chung với kỷ luật và trách nhiệm cao.',
        ],
    ],
    'PCLV' => [
        [
            'Hỏi nhu cầu của người liên quan rồi điều chỉnh theo họ.',
            'Phác thảo ý tưởng tự do rồi chọn hướng đẹp nhất.',
            'Xác định mục tiêu và lợi ích trước khi làm chi tiết.',
            'Đặt câu hỏi nghiên cứu, thu thập thông tin và lập kế hoạch kiểm chứng.',
            'Xem yêu cầu kỹ thuật, chuẩn bị dụng cụ rồi làm thử ngay.',
            'Nhận nhiệm vụ, xác nhận quy trình và triển khai đúng phân công.',
        ],
        [
            'Người kết nối và hỗ trợ thành viên.',
            'Người tạo ý tưởng sáng tạo và điểm nhấn.',
            'Người định hướng mục tiêu và thúc đẩy tiến độ.',
            'Người phân tích, kiểm chứng và đảm bảo lập luận chặt.',
            'Người thực thi kỹ thuật và kiểm tra vận hành.',
            'Người giữ kỷ luật nhóm, phân vai rõ và bảo đảm an toàn.',
        ],
        [
            'Sự hài lòng và cảm xúc của mọi người.',
            'Tính mới lạ và sức hấp dẫn của giải pháp.',
            'Hiệu quả và khả năng mang lại lợi ích nhanh.',
            'Độ vững của bằng chứng và tính tái lập kết quả.',
            'Tính khả thi kỹ thuật và độ ổn định khi vận hành.',
            'An toàn, đúng quy trình và khả năng phối hợp dưới áp lực.',
        ],
        [
            'Linh hoạt theo người và tình huống.',
            'Mở để còn chỗ cho sáng tạo đột phá.',
            'Rõ KPI và mốc bàn giao.',
            'Phương pháp nghiên cứu, tiêu chí đánh giá và lịch thu thập dữ liệu.',
            'Checklist thao tác kỹ thuật và mốc kiểm thử.',
            'Kế hoạch ca/phiên, phân cấp chỉ huy và quy trình ứng phó.',
        ],
        [
            'Tìm người hỗ trợ tinh thần rồi mới quay lại.',
            'Đổi hướng sang một ý tưởng khác thú vị hơn.',
            'Đánh giá lại mục tiêu và phân công lại việc.',
            'Thiết kế thêm phép thử để loại trừ giả thuyết sai.',
            'Thử – sai có hệ thống: đổi cấu hình, đo đạc, khoanh vùng lỗi.',
            'Báo cáo theo cấp, giữ vị trí và xử lý theo checklist khẩn.',
        ],
        [
            'Ai đó được giúp đỡ và cảm thấy tốt hơn.',
            'Có sản phẩm sáng tạo gây ấn tượng.',
            'Chốt được việc quan trọng hoặc tiến gần mục tiêu.',
            'Hiểu sâu hơn một vấn đề hoặc có phát hiện mới từ dữ liệu.',
            'Hoàn thành hạng mục kỹ thuật ổn định.',
            'Hoàn thành nhiệm vụ đúng giờ, đúng quy trình, không để xảy ra sự cố.',
        ],
        [
            'Cảm nhận con người và hoàn cảnh cụ thể.',
            'Trực giác thẩm mỹ và sự khác biệt của ý tưởng.',
            'Cơ hội – rủi ro – lợi ích.',
            'Bằng chứng, số liệu và suy luận logic.',
            'Thông số kỹ thuật và kết quả vận hành thử.',
            'Quy định, mệnh lệnh và đánh giá rủi ro an toàn.',
        ],
        [
            'Ổn định, có thời gian đồng hành lâu dài với người khác.',
            'Linh hoạt theo cảm hứng sáng tạo.',
            'Nhanh, nhiều quyết định và cơ hội.',
            'Tập trung sâu, ít bị ngắt quãng để suy nghĩ và phân tích.',
            'Theo ca/phiên gắn với tiến độ kỹ thuật thực tế.',
            'Theo ca trực, sẵn sàng ứng phó và duy trì kỷ luật tập thể.',
        ],
        [
            'Mức độ hài lòng của người được hỗ trợ.',
            'Chất lượng thẩm mỹ và tính sáng tạo.',
            'Kết quả kinh doanh và ảnh hưởng.',
            'Chất lượng phương pháp và độ vững của kết luận.',
            'Độ tin cậy kỹ thuật và an toàn vận hành.',
            'Mức độ tuân thủ quy trình, kỷ luật và hoàn thành sứ mệnh.',
        ],
        [
            'Tôi làm việc bằng sự thấu hiểu và hỗ trợ con người.',
            'Tôi làm việc bằng cảm hứng và sự thể hiện cá nhân.',
            'Tôi làm việc bằng mục tiêu, ảnh hưởng và kết quả.',
            'Tôi làm việc bằng phân tích và kiểm chứng khoa học.',
            'Tôi làm việc bằng thực hành kỹ thuật và tối ưu hệ thống.',
            'Tôi làm việc bằng kỷ luật, trách nhiệm và phối hợp đội ngũ dưới áp lực.',
        ],
    ],
];

/**
 * Với mỗi nhóm, lấy 5/6 phương án: luôn gồm phương án của nhóm (điểm 5),
 * và 4 phương án còn lại được gán điểm 1–4 theo khoảng cách slot.
 *
 * @param  list<string>  $six
 * @return list<array{noi_dung: string, diem: int}>
 */
$pickAnswersForNhom = static function (array $six, int $targetSlot): array {
    $scored = [];
    foreach ($six as $slot => $text) {
        $scored[] = [
            'noi_dung' => $text,
            'slot' => (int) $slot,
            'distance' => abs((int) $slot - $targetSlot),
        ];
    }

    // Giữ phương án của nhóm + 4 phương án gần nhất; bỏ 1 phương án xa nhất.
    usort($scored, static function (array $a, array $b) use ($targetSlot): int {
        if ($a['slot'] === $targetSlot) {
            return -1;
        }
        if ($b['slot'] === $targetSlot) {
            return 1;
        }
        if ($a['distance'] === $b['distance']) {
            return $a['slot'] <=> $b['slot'];
        }

        return $a['distance'] <=> $b['distance'];
    });

    $chosen = array_slice($scored, 0, 5);

    // Gán điểm 5→1 theo độ gần với nhóm mục tiêu.
    usort($chosen, static function (array $a, array $b) use ($targetSlot): int {
        if ($a['slot'] === $targetSlot) {
            return -1;
        }
        if ($b['slot'] === $targetSlot) {
            return 1;
        }
        if ($a['distance'] === $b['distance']) {
            return $a['slot'] <=> $b['slot'];
        }

        return $a['distance'] <=> $b['distance'];
    });

    $result = [];
    $points = [5, 4, 3, 2, 1];
    foreach ($chosen as $i => $item) {
        $result[] = [
            'noi_dung' => $item['noi_dung'],
            'diem' => $points[$i],
        ];
    }

    // Trộn thứ tự hiển thị để không lộ thang điểm (ổn định theo nội dung).
    usort($result, static fn (array $a, array $b): int => strcmp($a['noi_dung'], $b['noi_dung']));

    return $result;
};

$bank = [];
foreach ($orientationSlots as $nhomKey => $targetSlot) {
    $bank[$nhomKey] = [
        'matchers' => $nhomMatchers[$nhomKey],
        'items' => [],
    ];

    foreach ($questionsByLoai as $loaiMa => $questions) {
        $items = [];
        foreach ($questions as $qIndex => $cauHoi) {
            $six = $sixWayAnswers[$loaiMa][$qIndex] ?? null;
            if (! is_array($six) || count($six) < 6) {
                continue;
            }

            $items[] = [
                'cau_hoi' => $cauHoi,
                'dap_an' => $pickAnswersForNhom($six, $targetSlot),
            ];
        }
        $bank[$nhomKey]['items'][$loaiMa] = $items;
    }
}

return $bank;
