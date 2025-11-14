<?php

namespace App\Livewire\Crud;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Agency;

class AgencyManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateForm = false;
    public $showEditForm = false;
    public $showDeleted = false;
    public $name;
    public $code;
    public $editingId;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:agencies,code',
    ];

    protected $listeners = ['confirmDeleteAgency' => 'delete'];


    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->resetForm();
    }

    public function toggleShowDeleted()
    {
        $this->showDeleted = !$this->showDeleted;
    }

    public function create()
    {
        $this->validate();

        Agency::create([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        session()->flash('message', 'Agenzia creata con successo.');
        $this->resetForm();
        $this->showCreateForm = false;
    }

    public function edit($id)
    {
        $agency = Agency::findOrFail($id);
        $this->editingId = $id;
        $this->name = $agency->name;
        $this->code = $agency->code;
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:agencies,code,' . $this->editingId,
        ]);

        $agency = Agency::findOrFail($this->editingId);
        $agency->update([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        session()->flash('message', 'Agenzia aggiornata con successo.');
        $this->resetForm();
    }

    public function delete($id)
    {
        $agency = Agency::findOrFail($id);
        $agency->delete();
        session()->flash('message', 'Agenzia eliminata con successo.');
    }

    public function restore($id)
    {
        $agency = Agency::withTrashed()->findOrFail($id);
        $agency->restore();
        session()->flash('message', 'Agenzia ripristinata con successo.');
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'editingId', 'showEditForm']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
{
    $this->dispatch('openConfirmModal', [
        'message'      => 'Eliminare questa agenzia?',
        'confirmEvent' => 'confirmDeleteAgency',
        'payload'       => $id,
    ]);
}

    public function render()
    {
        $query = Agency::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->showDeleted) {
            $query->withTrashed();
        }

        $agencies = $query->paginate(10);

        return view('livewire.crud.agency-manager', compact('agencies'));
    }
}