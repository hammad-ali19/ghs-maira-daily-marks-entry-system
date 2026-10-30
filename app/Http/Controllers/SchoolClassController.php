<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the school classes.
     */
    public function index(): View
    {
        $classes = SchoolClass::with(['sections.incharge', 'sections.currentEnrollments'])
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15);

        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new school class.
     */
    public function create(): View
    {
        $teachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('name')->get();

        return view('classes.create', compact('teachers'));
    }

    /**
     * Store a newly created school class in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.name' => ['required', 'string', 'max:50'],
            'sections.*.incharge_id' => ['nullable', 'exists:users,id', 'distinct',
            // Dynamic validation to check DB table while ignoring the specific section row
            function ($attribute, $value, $fail) use ($request) {
                // Parse the index from the attribute string (e.g., 'sections.0.incharge_id' -> index 0)
                preg_match('/sections\.(\d+)\.incharge_id/', $attribute, $matches);
                $index = $matches[1] ?? null;

                if ($index !== null) {
                    $sectionId = $request->input("sections.{$index}.id");

                    // Check if this user is assigned to any other section
                    $exists = \App\Models\ClassSection::where('incharge_id', $value)
                        ->when($sectionId, function ($query) use ($sectionId) {
                            return $query->where('id', '!=', $sectionId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The selected incharge is already assigned to another class section.');
                    }
                }
            },
            ],
        ]);

        $schoolClass = SchoolClass::create([
            'name' => $validated['name'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        foreach ($validated['sections'] as $sectionData) {
            $schoolClass->sections()->create([
                'name' => trim($sectionData['name']),
                'incharge_id' => ! empty($sectionData['incharge_id']) ? $sectionData['incharge_id'] : null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('classes.index')->with('success', "Class '{$schoolClass->name}' created successfully with sections.");
    }

    /**
     * Display the specified school class.
     */
    public function show(SchoolClass $class): View
    {
        $class->load([
            'sections.incharge',
            'sections.currentEnrollments.student',
            'sections.classSectionSubjects.subject',
        ]);

        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified school class.
     */
    public function edit(SchoolClass $class): View
    {
        $class->load('sections.incharge');
        $teachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('name')->get();

        return view('classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified school class in storage.
     */
    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'exists:class_sections,id'],
            'sections.*.name' => ['required_with:sections', 'string', 'max:50'],
            'sections.*.incharge_id' => ['nullable', 'exists:users,id', 'distinct', 
            // Dynamic validation to check DB table while ignoring the specific section row
            function ($attribute, $value, $fail) use ($request) {
                // Parse the index from the attribute string (e.g., 'sections.0.incharge_id' -> index 0)
                preg_match('/sections\.(\d+)\.incharge_id/', $attribute, $matches);
                $index = $matches[1] ?? null;

                if ($index !== null) {
                    $sectionId = $request->input("sections.{$index}.id");

                    // Check if this user is assigned to any other section
                    $exists = \App\Models\ClassSection::where('incharge_id', $value)
                        ->when($sectionId, function ($query) use ($sectionId) {
                            return $query->where('id', '!=', $sectionId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The selected incharge is already assigned to another class section.');
                    }
                }
            },
            ],
            'sections.*.is_active' => ['nullable', 'boolean'],
        ]);

        $class->update([
            'name' => $validated['name'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ]);

        if (! empty($validated['sections'])) {
            foreach ($validated['sections'] as $sec) {
                if (! empty($sec['id'])) {
                    $section = ClassSection::where('school_class_id', $class->id)->find($sec['id']);
                    if ($section) {
                        $section->update([
                            'name' => trim($sec['name']),
                            'incharge_id' => ! empty($sec['incharge_id']) ? $sec['incharge_id'] : null,
                            'is_active' => isset($sec['is_active']) ? (bool) $sec['is_active'] : true,
                        ]);
                    }
                } else {
                    $class->sections()->create([
                        'name' => trim($sec['name']),
                        'incharge_id' => ! empty($sec['incharge_id']) ? $sec['incharge_id'] : null,
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('classes.index')->with('success', "Class '{$class->name}' updated successfully.");
    }

    /**
     * Toggle or deactivate a class safely.
     */
    public function destroy(SchoolClass $class): RedirectResponse
    {
        // Check if there are historical tests in any of this class's sections
        $hasTests = ClassSection::where('school_class_id', $class->id)
            ->whereHas('classSectionSubjects.tests')
            ->exists();

        if ($hasTests) {
            // Safely deactivate instead of deleting to preserve historical test records
            $class->update(['is_active' => false]);
            $class->sections()->update(['is_active' => false]);

            return redirect()->route('classes.index')->with('warning', "Class '{$class->name}' has historical test records. It was deactivated instead of deleted to protect historical data.");
        }

        $class->sections()->delete();
        $class->delete();

        return redirect()->route('classes.index')->with('success', "Class '{$class->name}' was deleted successfully.");
    }
}
