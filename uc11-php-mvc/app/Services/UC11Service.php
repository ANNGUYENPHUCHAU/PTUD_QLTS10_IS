<?php
declare(strict_types=1);

/** Service: quy tắc nghiệp vụ UC11, không chứa truy vấn SQL. */
final class UC11Service
{
    public function __construct(private UC11Model $model) {}
    public function all(): array { return $this->model->all(); }
    public function find(int $id): ?array { return $this->model->find($id); }
    public function logs(): array { return $this->model->logs(); }

    private function date(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count']))
            || $date->format('Y-m-d\TH:i') !== $value) {
            throw new DomainException('Nhập đủ bốn mốc ngày giờ hợp lệ.');
        }
        return $date;
    }

    private function versioned(?array $round, int $version): array
    {
        if (!$round || (int)$round['version'] !== $version) {
            throw new DomainException('Dữ liệu đã thay đổi hoặc không tồn tại. Hãy tải lại danh sách.');
        }
        return $round;
    }

    public function recordLogout(string $actor): void
    {
        $this->model->record(null, 'Đăng xuất', $actor);
    }

    public function save(array $data, string $actor): string
    {
        return $this->model->transaction(function () use ($data, $actor): string {
            $id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            $version = filter_var($data['version'] ?? 0, FILTER_VALIDATE_INT);
            if ($id === false || $version === false || $id < 0 || $version < 0) {
                throw new DomainException('Mã đợt hoặc phiên bản không hợp lệ.');
            }
            if ($id) {
                $old = $this->versioned($this->model->find($id), $version);
                if ($old['status'] !== 'Chưa mở') {
                    throw new DomainException('Chỉ được điều chỉnh đợt chưa mở.');
                }
            }
            $name = trim((string)($data['name'] ?? ''));
            $length = preg_match_all('/./us', $name);
            if ($name === '' || $length === false || $length > 150) {
                throw new DomainException('Tên đợt bắt buộc và không quá 150 ký tự.');
            }
            $keys = ['opens', 'closes', 'selection', 'confirmation'];
            $dates = array_map(fn(string $key): DateTimeImmutable => $this->date((string)($data[$key] ?? '')), $keys);
            for ($i = 0; $i < 3; $i++) {
                if ($dates[$i] >= $dates[$i + 1]) {
                    throw new DomainException('Cần: mở đăng ký < đóng đăng ký < xét tuyển < xác nhận nhập học.');
                }
            }
            $values = array_map(fn(DateTimeImmutable $date): string => $date->format('Y-m-d\TH:i'), $dates);
            $conflict = $this->model->conflict($id, $values[0], $values[3]);
            if ($conflict) {
                throw new DomainException('Lịch tuyển sinh bị trùng với đợt: ' . $conflict['name']);
            }
            if ($id) {
                $this->model->update($id, $name, $values);
                $action = 'Điều chỉnh lịch';
                $message = 'Điều chỉnh lịch tuyển sinh thành công.';
            } else {
                $id = $this->model->insert($name, $values);
                $action = 'Tạo đợt';
                $message = 'Tạo đợt tuyển sinh thành công.';
            }
            $this->model->record($id, $action, $actor);
            return $message;
        });
    }

    public function changeStatus(int $id, int $version, string $action, string $actor, ?DateTimeImmutable $now = null): string
    {
        return $this->model->transaction(function () use ($id, $version, $action, $actor, $now): string {
            $round = $this->versioned($this->model->find($id), $version);
            $now = $now ?? new DateTimeImmutable();
            if ($action === 'open') {
                if ($round['status'] !== 'Chưa mở' || $now < $this->date($round['opens']) || $now >= $this->date($round['closes'])) {
                    throw new DomainException('Chỉ mở đợt chưa mở khi đã đến giờ mở và chưa đến giờ đóng đăng ký.');
                }
                $status = 'Đang mở';
                $message = 'Mở đợt tuyển sinh thành công.';
            } elseif ($action === 'close') {
                if ($round['status'] !== 'Đang mở' || $now < $this->date($round['closes'])) {
                    throw new DomainException('Chỉ đóng đợt đang mở khi đã đến giờ đóng đăng ký.');
                }
                $status = 'Đã đóng';
                $message = 'Đóng đợt tuyển sinh thành công.';
            } else {
                throw new DomainException('Thao tác trạng thái không hợp lệ.');
            }
            $this->model->updateStatus($id, $status);
            $this->model->record($id, $status, $actor, $now);
            return $message;
        });
    }
}
