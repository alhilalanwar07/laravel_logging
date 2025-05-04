<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\File;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $logs;
    public $paginate = 10;
    public $search = '';
    public $page = 1;
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->logs = collect($this->getLogs()); // pastikan jadi Collection
    }

    public function updatingSearch()
    {
        $this->page = 1;
    }

    public function updatedPaginate()
    {
        $this->page = 1;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function getLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            $logs = array_reverse(file($logFile));
        }

        return collect($logs);
    }

    public function getFilteredLogs()
    {
        $logs = $this->getLogs();

        $filtered = $logs->filter(function ($line) {
            return stripos($line, $this->search) !== false;
        })->values();

        $offset = ($this->page - 1) * $this->paginate;
        return $filtered->slice($offset, $this->paginate)->all();
    }

    public function with(): array
    {
        $logs = $this->getFilteredLogs();
        $total = $this->getLogs()->filter(function ($line) {
            return stripos($line, $this->search) !== false;
        })->count();
        $lastPage = ceil($total / $this->paginate);

        return [
            'logs' => $logs,
            'page' => $this->page,
            'lastPage' => $lastPage,
            'total' => $total,
        ];
    }
}; ?>

<div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Log Aplikasi
                </div>

                <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between">
                        <input type="text" class="form-control w-50" placeholder="Cari pesan / level / pengguna..." wire:model.live="search">
                        <select class="form-select w-auto ms-3" wire:model.live="paginate">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Level</th>
                                    <th>Pesan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                @php
                                $timestamp = null;
                                $level = null;
                                $message = null;
                                $user = null;

                                preg_match('/\[(.*?)\] (\w+)\.(\w+): (.*)/', $log, $matches);
                                if (count($matches) >= 5) {
                                $timestamp = $matches[1];
                                $level = $matches[3];
                                $message = $matches[4];
                                $user = $matches[2] ?? null;
                                $user = str_replace(' ', '', $user);
                                }
                                @endphp

                                @if(isset($timestamp) && isset($level) && isset($message))
                                @php
                                $searchLower = strtolower($search);
                                $combined = strtolower($timestamp . ' ' . $level . ' ' . $message . ' ' . $user);
                                @endphp

                                @if ($search == '' || str_contains($combined, $searchLower))
                                <tr class="{{ $level === 'CRITICAL' ? 'table-danger' : '' }}">
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ \Carbon\Carbon::parse($timestamp)->format('d-m-Y H:i:s') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $level === 'CRITICAL' ? 'danger' : 'info' }}">
                                            {{ $level }}
                                        </span>
                                    </td>
                                    <td class="text-wrap">{{ $message }}</td>
                                </tr>
                                @endif
                                @endif
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada log ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn btn-secondary" wire:click="prevPage" @if($page <= 1) disabled @endif>Prev</button>
                        <span>Halaman {{ $page }} dari {{ $lastPage }}</span>
                        <button class="btn btn-secondary" wire:click="nextPage" @if($page >= $lastPage) disabled @endif>Next</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>