<?php
declare(strict_types=1);

final class UC19Service
{
    // Quy tắc đã thiết lập cho bản demo: số ngẫu nhiên 9 chữ số.
    public const CODE_DIGITS = 9;

    public static function issues(array $bag, array $entries): array
    {
        $issues = [];
        if ($bag['subject_type'] !== 'essay') $issues[] = 'Chỉ quản lý phách cho môn thi tự luận.';
        if (!$entries) $issues[] = 'Túi chấm không có bài thi.';
        if (!$bag['handover_ref']) $issues[] = 'Thiếu biên bản bàn giao của túi chấm.';
        if ($bag['paper_count'] === null || (int)$bag['paper_count'] !== count($entries)) {
            $issues[] = 'Số bài không khớp dữ liệu bàn giao của túi chấm.';
        }
        $sheets = 0;
        $seen = [];
        foreach ($entries as $entry) {
            if (!$entry['student_id'] || !$entry['candidate_number']
                || !$entry['room_id'] || $entry['attendance'] !== 'present') {
                $issues[] = 'Thiếu hoặc sai thông tin đối chiếu của bài thi.';
            }
            if ((int)$entry['scope_id'] !== (int)$bag['scope_id']) {
                $issues[] = 'Bài thi không thuộc kỳ thi và môn thi của túi chấm.';
            }
            if ($entry['sheet_count'] === null || (int)$entry['sheet_count'] < 1) {
                $issues[] = 'Số tờ giấy thi của bài thi phải là số nguyên dương.';
            } else {
                $sheets += (int)$entry['sheet_count'];
            }
            if ($entry['conflict'] || isset($seen[$entry['student_id']])) {
                $issues[] = 'Bài thi đã thuộc túi chấm khác trong cùng kỳ thi và môn thi.';
            }
            $seen[$entry['student_id']] = true;
        }
        if ($bag['sheet_count'] === null || (int)$bag['sheet_count'] !== $sheets) {
            $issues[] = 'Tổng số tờ giấy thi không khớp dữ liệu bàn giao của túi chấm.';
        }
        return array_values(array_unique($issues));
    }

    public static function validate(array $bag, array $entries): void
    {
        if ($bag['status'] !== 'pending') throw new DomainException('Túi chấm đã được tạo phách');
        $issues = self::issues($bag, $entries);
        if ($issues) throw new DomainException(implode(' ', $issues) . ' Cần kiểm tra túi chấm.');
    }

    public static function fingerprint(array $bag, array $entries): string
    {
        return hash('sha256', serialize([$bag, $entries]));
    }

    public static function generate(array $entries, array $used, ?callable $random = null): array
    {
        $random ??= static fn(): string => (string)random_int(100000000, 999999999);
        $reserved = array_fill_keys($used, true);
        $result = [];
        foreach ($entries as $entry) {
            for ($attempt = 0; $attempt < 1000; $attempt++) {
                $code = (string)$random();
                if (preg_match('/^[1-9][0-9]{8}$/D', $code) && !isset($reserved[$code])) {
                    $result[(int)$entry['id']] = $code;
                    $reserved[$code] = true;
                    continue 2;
                }
            }
            throw new DomainException('Chưa sinh được số phách duy nhất. Vui lòng thử lại.');
        }
        return $result;
    }

    public static function validateCodes(array $entries, array $codes, array $used): void
    {
        $ids = array_map('intval', array_column($entries, 'id'));
        $keys = array_keys($codes);
        sort($ids);
        sort($keys);
        if ($ids !== $keys || count(array_unique($codes)) !== count($codes) || array_intersect($codes, $used)) {
            throw new DomainException('Bản xem trước không còn hợp lệ hoặc số phách bị trùng. Hãy tạo lại phách.');
        }
        foreach ($codes as $code) {
            if (!is_string($code) || !preg_match('/^[1-9][0-9]{8}$/D', $code)) {
                throw new DomainException('Số phách không hợp lệ.');
            }
        }
    }
}
