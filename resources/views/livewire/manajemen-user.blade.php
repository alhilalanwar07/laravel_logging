<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $paginate = 10;

    public function with(): array
    {
        return [
            'users' => User::paginate($this->paginate)
        ];
    }

    // create user
    public $name, $username, $email, $password, $role;

    public function store()
    {
        $this->validate([
            'name' => 'required',
            // 'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            // 'username.required' => 'Username tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'password.required' => 'Password tidak boleh kosong',
            'role.required' => 'Role tidak boleh kosong'
        ]);

        // dd($this->name, $this->email, $this->password, $this->role);

        try {
            User::create([
                'name' => $this->name,
                // 'username' => $this->username,
                'email' => $this->email,
                'password' => bcrypt($this->password),
                'role' => $this->role,
                // 'status' => 'active'
            ]);

            FacadesLog::info(Auth::user()->name . ' created a new user: ' . $this->name);

            $this->reset();
            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    // hapus
    public $user_id;
    public function delete($id)
    {
        $this->user_id = $id;
    }
    public function destroy()
    {
        try {
            $user = User::find($this->user_id);
            $user->delete();
            
            FacadesLog::critical('User deleted successfully: ' . $user->name);
            
            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            FacadesLog::error('Failed to delete user: ' . $e->getMessage());
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }
}; ?>

<div>
<div>
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">User</div>
                    <div class="card-tools">
                        <a href="#" class="btn btn-info  btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addUser">
                            <span class="btn-label">
                                <i class="fa fa-plus"></i>
                            </span>
                            Tambah User
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary m-1">Edit</a>
                                    <button wire:click="delete({{ $user->id }})" class="btn btn-sm btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteUser">Delete</button>

                                    <!-- Modal -->  
                                    <div wire:ignore.self class="modal fade" id="deleteUser" tabindex="-1" aria-labelledby="deleteUserLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteUserLabel">Hapus User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus user ini?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                                                    <button wire:click="destroy()" type="button" class="btn btn-danger">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore class="modal fade" id="addUser" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserLabel">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                <form wire:submit.prevent="store()">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Name" wire:model="name">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Email" wire:model="email">
                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password" wire:model="password">
                        @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" wire:model="role">
                            <option selected>Pilih...</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                        @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit"  class="btn btn-primary">Simpan</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <livewire:_alert />
</div>
</div>
