<?php

namespace App\Http\Controllers;

use App\Factory\EmployeeFactory;
use App\Filters\EmployeeFilters;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use App\Transformers\EmployeeTransformer;
use App\Utils\Traits\MakesHash;
use Illuminate\Http\Response;

class EmployeeController extends BaseController
{
    use MakesHash;

    protected $entity_type = Employee::class;
    protected $entity_transformer = EmployeeTransformer::class;

    public function __construct(protected EmployeeRepository $employee_repo)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param EmployeeFilters $filters
     * @return Response
     */
    public function index(EmployeeFilters $filters)
    {
        $employees = Employee::filter($filters);

        return $this->listResponse($employees);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $employee = EmployeeFactory::create(auth()->user()->company()->id, auth()->user()->id);

        return $this->itemResponse($employee);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEmployeeRequest $request
     * @return Response
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = $this->employee_repo->save($request->all(), EmployeeFactory::create(auth()->user()->company()->id, auth()->user()->id));

        return $this->itemResponse($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function show(Employee $employee)
    {
        return $this->itemResponse($employee);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function edit(Employee $employee)
    {
        return $this->itemResponse($employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateEmployeeRequest $request
     * @param Employee $employee
     * @return Response
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee = $this->employee_repo->save($request->all(), $employee);

        return $this->itemResponse($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Employee $employee
     * @return Response
     */
    public function destroy(Employee $employee)
    {
        $employee->is_deleted = true;
        $employee->save();

        return $this->itemResponse($employee);
    }

    /**
     * Perform bulk actions on the list view.
     *
     * @return Response
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('ids');

        $employees = Employee::withTrashed()->find($this->transformKeys($ids));

        $employees->each(function ($employee, $key) use ($action) {
            if (auth()->user()->can('edit', $employee)) {
                $this->performAction($employee, $action, true);
            }
        });

        return $this->listResponse(Employee::withTrashed()->whereIn('id', $this->transformKeys($ids)));
    }

    private function performAction(Employee $employee, string $action, bool $bulk = false)
    {
        switch ($action) {
            case 'archive':
                $employee->delete();
                break;
            case 'restore':
                $employee->restore();
                break;
            case 'delete':
                $employee->is_deleted = true;
                $employee->save();
                break;
            case 'activate':
                $employee->status = 'active';
                $employee->save();
                break;
            case 'deactivate':
                $employee->status = 'inactive';
                $employee->save();
                break;
            default:
                return;
        }
    }
}