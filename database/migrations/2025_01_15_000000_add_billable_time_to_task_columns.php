<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing companies to include billable_time in their task_columns configuration
        Company::whereNotNull('settings')->chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $settings = $company->settings;
                
                // Check if pdf_variables exists and has task_columns
                if (isset($settings->pdf_variables) && isset($settings->pdf_variables->task_columns)) {
                    $taskColumns = $settings->pdf_variables->task_columns;
                    
                    // Check if billable_time is not already in the task_columns
                    if (!in_array('$task.billable_time', $taskColumns)) {
                        // Find the position after $task.hours or $task.quantity
                        $insertPosition = -1;
                        
                        foreach ($taskColumns as $index => $column) {
                            if ($column === '$task.hours' || $column === '$task.quantity') {
                                $insertPosition = $index + 1;
                                break;
                            }
                        }
                        
                        // If we found a position, insert billable_time there
                        if ($insertPosition !== -1) {
                            array_splice($taskColumns, $insertPosition, 0, ['$task.billable_time']);
                        } else {
                            // Otherwise, add it before discount if it exists
                            $discountPosition = array_search('$task.discount', $taskColumns);
                            if ($discountPosition !== false) {
                                array_splice($taskColumns, $discountPosition, 0, ['$task.billable_time']);
                            } else {
                                // As a fallback, add it at the end before line_total
                                $lineTotalPosition = array_search('$task.line_total', $taskColumns);
                                if ($lineTotalPosition !== false) {
                                    array_splice($taskColumns, $lineTotalPosition, 0, ['$task.billable_time']);
                                } else {
                                    // Ultimate fallback - add at the end
                                    $taskColumns[] = '$task.billable_time';
                                }
                            }
                        }
                        
                        // Update the settings
                        $settings->pdf_variables->task_columns = $taskColumns;
                        $company->settings = $settings;
                        $company->save();
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove billable_time from existing companies' task_columns
        Company::whereNotNull('settings')->chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $settings = $company->settings;
                
                // Check if pdf_variables exists and has task_columns
                if (isset($settings->pdf_variables) && isset($settings->pdf_variables->task_columns)) {
                    $taskColumns = $settings->pdf_variables->task_columns;
                    
                    // Remove billable_time from task_columns
                    $taskColumns = array_filter($taskColumns, function($column) {
                        return $column !== '$task.billable_time';
                    });
                    
                    // Reindex the array
                    $taskColumns = array_values($taskColumns);
                    
                    // Update the settings
                    $settings->pdf_variables->task_columns = $taskColumns;
                    $company->settings = $settings;
                    $company->save();
                }
            }
        });
    }
}; 