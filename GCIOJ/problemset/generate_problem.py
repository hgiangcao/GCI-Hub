import zipfile
import os
import random
import math

# Define the base templates
html_template = """<div class="space-y-4 text-white">
    <p>
        {description}
    </p>

    <p>
        You must define the function <code class="bg-white-700 px-1 py-0.5 rounded text-brand-orange text-xs font-mono">{func_sig}</code>.
    </p>

    <br>
    <hr>
    <div class="mt-6">
        <h3 class="text-white font-bold text-sm mb-2">Example 1:</h3>
        <div class="bg-[#282828] border border-gray-700 rounded-lg p-3 font-mono text-sm leading-relaxed">
            <div class="flex gap-2">
                <span class="text-white font-bold select-none">Input:</span>
                <span class="text-white">{ex1_in}</span>
            </div>
            <div class="flex gap-2">
                <span class="text-white font-bold select-none">Output:</span>
                <span class="text-brand-green font-bold">{ex1_out}</span>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h3 class="text-white font-bold text-sm mb-2">Example 2:</h3>
        <div class="bg-[#282828] border border-gray-700 rounded-lg p-3 font-mono text-sm leading-relaxed">
            <div class="flex gap-2">
                <span class="text-white select-none">Input:</span>
                <span class="text-white">{ex2_in}</span>
            </div>
            <div class="flex gap-2">
                <span class="text-white select-none">Output:</span>
                <span class="text-brand-green font-bold">{ex2_out}</span>
            </div>
        </div>
    </div>

    <div class="mt-6 text-white">
        <h3 class="text-white font-bold text-sm mb-2">Constraints:</h3>
        <ul class="list-disc list-inside  text-sm space-y-1 ml-1">
            {constraints}
        </ul>
    </div>
</div>"""

py_template = """import random
random.seed(42)

def generate_int_num(min_val=0, max_val=1000):
    return random.randint(min_val, max_val)

def generate_int_arr(n_num=20, min_val=-1000, max_val=1000):
    return [random.randint(min_val, max_val) for _ in range(n_num)]

{extra_generators}

def correct_solution({args}):
    {solution_body}

def auto_grade():
    score = 0
    n_test = 100
    status = "Pending"
    
    try:
        for i in range(1, n_test + 1):
            # Generate random test cases
            {test_generation}

            try:
                # Create a copy so student code doesn't mess up original data
                {copy_logic}

                # Run functions
                student_ans = solve({student_call_args})
                correct_ans = correct_solution({correct_call_args})

                if student_ans == correct_ans:
                    score += 1
            except Exception as e:
                status = "Compile Error"
    except Exception as e:
        status = "Compile Error"

    print(f"Final Score: {{score}} / {{n_test}}")

    if (score == n_test):
        status = "Accepted"
    elif (status != "Compile Error"):
        status = "Wrong Answer"

    print("Status:", status)

if "solve" in globals():
    try:
        auto_grade()
    except Exception as e:
        print("Status:", "Compile Error")
else:
    print("Compilation Error: Function 'solve' is not defined.")
    print("Status:", "Compile Error")
"""

# Define Problem Metadata
problems = [
    {
        "code": "G002_FINDMIN",
        "title": "Find Minimum Value in List",
        "desc": "Given a list of integers, return the smallest integer in the list.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 5, 3, 9, 2]", "ex1_out": "1",
        "ex2_in": "number = [-10, -5, -2]", "ex2_out": "-10",
        "constraints": "<li>The list will contain at least one number.</li>",
        "py_args": "number",
        "py_sol": "return min(number)",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G003_MAXFOUR",
        "title": "Find Maximum of Four Numbers",
        "desc": "Given a list of exactly four integers, return the largest integer.",
        "func": "solve(number)",
        "ex1_in": "number = [10, 20, 5, 30]", "ex1_out": "30",
        "ex2_in": "number = [-1, -5, -3, -2]", "ex2_out": "-1",
        "constraints": "<li>The list will always contain exactly 4 integers.</li>",
        "py_args": "number",
        "py_sol": "return max(number)",
        "py_gen": "test_input = generate_int_arr(n_num=4)",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G004_DIFFMAXMIN",
        "title": "Difference Between Max and Min",
        "desc": "Given a list of integers, return the difference between the largest and smallest values.",
        "func": "solve(number)",
        "ex1_in": "number = [10, 3, 5, 2]", "ex1_out": "8", # 10 - 2
        "ex2_in": "number = [-5, -10, -2]", "ex2_out": "8", # -2 - (-10) = 8
        "constraints": "<li>List size >= 2</li>",
        "py_args": "number",
        "py_sol": "return max(number) - min(number)",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G005_SECSMALL",
        "title": "Find Second Smallest Number",
        "desc": "Given a list of integers, return the second smallest number. Assume the list has at least two numbers.",
        "func": "solve(number)",
        "ex1_in": "number = [5, 1, 4, 2]", "ex1_out": "2",
        "ex2_in": "number = [10, 10, 20]", "ex2_out": "10",
        "constraints": "<li>List size >= 2</li><li>Return the 2nd element in sorted order (duplicates allowed).</li>",
        "py_args": "number",
        "py_sol": "return sorted(number)[1]",
        "py_gen": "test_input = generate_int_arr(n_num=20)",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G006_LARGEEVEN",
        "title": "Largest Even Number in Array",
        "desc": "Given a list of integers, return the largest even number. If no even number exists, return -1.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 3, 5, 8, 4]", "ex1_out": "8",
        "ex2_in": "number = [1, 3, 5]", "ex2_out": "-1",
        "constraints": "<li>Integers can be negative.</li>",
        "py_args": "number",
        "py_sol": "evens = [x for x in number if x % 2 == 0]\n    return max(evens) if evens else -1",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G007_SMALLODD",
        "title": "Smallest Odd Number in List",
        "desc": "Given a list of integers, return the smallest odd number. If no odd number exists, return -1.",
        "func": "solve(number)",
        "ex1_in": "number = [2, 4, 3, 9, 1]", "ex1_out": "1",
        "ex2_in": "number = [2, 4, 8]", "ex2_out": "-1",
        "constraints": "<li>Integers can be negative.</li>",
        "py_args": "number",
        "py_sol": "odds = [x for x in number if x % 2 != 0]\n    return min(odds) if odds else -1",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G008_POSDIV5",
        "title": "Check Positive and Divisible by 5",
        "desc": "Given an integer n, return True if it is positive (>0) and divisible by 5, otherwise False.",
        "func": "solve(n)",
        "ex1_in": "n = 25", "ex1_out": "True",
        "ex2_in": "n = -25", "ex2_out": "False",
        "constraints": "<li>Input is a single integer.</li>",
        "py_args": "n",
        "py_sol": "return n > 0 and n % 5 == 0",
        "py_gen": "test_input = generate_int_num(-100, 100)",
        "py_copy": "student_input_copy = test_input",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G009_SUMNEG",
        "title": "Sum of All Negative Numbers",
        "desc": "Given a list of integers, return the sum of all negative numbers.",
        "func": "solve(number)",
        "ex1_in": "number = [1, -2, 3, -4]", "ex1_out": "-6",
        "ex2_in": "number = [1, 2, 3]", "ex2_out": "0",
        "constraints": "<li>Return 0 if no negative numbers.</li>",
        "py_args": "number",
        "py_sol": "return sum(x for x in number if x < 0)",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G010_COUNT37",
        "title": "Count Numbers Divisible by 3 or 7",
        "desc": "Given a list of integers, return the count of numbers that are divisible by either 3 or 7.",
        "func": "solve(number)",
        "ex1_in": "number = [3, 7, 21, 4, 5]", "ex1_out": "3", # 3, 7, 21
        "ex2_in": "number = [1, 2, 4]", "ex2_out": "0",
        "constraints": "<li>Zero is divisible by everything, treat 0 as divisible.</li>",
        "py_args": "number",
        "py_sol": "return len([x for x in number if x % 3 == 0 or x % 7 == 0])",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G011_AVGPOS",
        "title": "Average of Positive Integers",
        "desc": "Given a list of integers, calculate the average of all positive (>0) integers. Return 0 if there are no positive integers.",
        "func": "solve(number)",
        "ex1_in": "number = [10, 20, -5]", "ex1_out": "15.0",
        "ex2_in": "number = [-1, -2]", "ex2_out": "0",
        "constraints": "<li>Return float value.</li>",
        "py_args": "number",
        "py_sol": "pos = [x for x in number if x > 0]\n    return sum(pos)/len(pos) if pos else 0",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G012_FIRSTNONPOS",
        "title": "Find First Non-Positive Number",
        "desc": "Given a list of integers, return the first number that is not positive (<= 0). Assume at least one exists.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 5, 0, 3, -2]", "ex1_out": "0",
        "ex2_in": "number = [10, -5, 2]", "ex2_out": "-5",
        "constraints": "<li>List contains at least one non-positive number.</li>",
        "py_args": "number",
        "py_sol": "for x in number:\n        if x <= 0: return x\n    return 0",
        "py_gen": "test_input = generate_int_arr(); test_input.append(-1); random.shuffle(test_input)",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G013_SUMEVENRANGE",
        "title": "Sum of Even Numbers in Range",
        "desc": "Given two integers start and end, return the sum of all even numbers in the range [start, end] (inclusive).",
        "func": "solve(start, end)",
        "ex1_in": "start = 1, end = 5", "ex1_out": "6", # 2 + 4
        "ex2_in": "start = 2, end = 4", "ex2_out": "6", # 2 + 4
        "constraints": "<li>start <= end</li>",
        "py_args": "start, end",
        "py_sol": "return sum(x for x in range(start, end + 1) if x % 2 == 0)",
        "py_gen": "start = generate_int_num(0, 50); end = generate_int_num(start, start + 50)",
        "py_copy": "pass",
        "py_scall": "start, end",
        "py_ccall": "start, end"
    },
    {
        "code": "G014_COUNTGTAVG",
        "title": "Count Elements Greater Than Average",
        "desc": "Given a list of integers, return the count of numbers that are strictly greater than the average of the list.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 2, 3]", "ex1_out": "1", # Avg 2, 3 > 2
        "ex2_in": "number = [1, 1, 1]", "ex2_out": "0",
        "constraints": "<li>List is not empty.</li>",
        "py_args": "number",
        "py_sol": "avg = sum(number) / len(number)\n    return len([x for x in number if x > avg])",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G015_PRODNONZERO",
        "title": "Product of Non-Zero Numbers",
        "desc": "Given a list of integers, return the product of all non-zero numbers. If no non-zero numbers exist, return 0.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 2, 0, 3]", "ex1_out": "6",
        "ex2_in": "number = [0, 0, 0]", "ex2_out": "0",
        "constraints": "<li>Integers can be negative.</li>",
        "py_args": "number",
        "py_sol": "prod = 1\n    non_zeros = [x for x in number if x != 0]\n    if not non_zeros: return 0\n    for x in non_zeros: prod *= x\n    return prod",
        "py_gen": "test_input = [random.randint(0, 5) for _ in range(10)]", # Smaller numbers to prevent overflow
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G016_CHECKNEG",
        "title": "Check for Negative Inputs",
        "desc": "Given a list of integers, return True if the list contains at least one negative number, otherwise False.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 2, -3]", "ex1_out": "True",
        "ex2_in": "number = [1, 2, 3]", "ex2_out": "False",
        "constraints": "<li>List is not empty.</li>",
        "py_args": "number",
        "py_sol": "return any(x < 0 for x in number)",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G017_SUMSQPOS",
        "title": "Sum of Squares of Positive Numbers",
        "desc": "Given a list of integers, return the sum of the squares of all positive numbers.",
        "func": "solve(number)",
        "ex1_in": "number = [1, -2, 3]", "ex1_out": "10", # 1^2 + 3^2
        "ex2_in": "number = [-1, -2]", "ex2_out": "0",
        "constraints": "<li>Ignore 0 and negative numbers.</li>",
        "py_args": "number",
        "py_sol": "return sum(x**2 for x in number if x > 0)",
        "py_gen": "test_input = [random.randint(-10, 10) for _ in range(10)]",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G018_DIVXY",
        "title": "Find Number Divisible by X and Y",
        "desc": "Given a list of integers and integers X and Y, return the first number in the list divisible by both X and Y. Return -1 if none found.",
        "func": "solve(number, x, y)",
        "ex1_in": "number = [10, 12, 30], x=2, y=3", "ex1_out": "12", # 12 % 6 == 0
        "ex2_in": "number = [10, 15], x=7, y=2", "ex2_out": "-1",
        "constraints": "<li>x and y are non-zero.</li>",
        "py_args": "number, x, y",
        "py_sol": "for n in number:\n        if n % x == 0 and n % y == 0: return n\n    return -1",
        "py_gen": "test_input = generate_int_arr(); x = generate_int_num(2, 5); y = generate_int_num(2, 5)",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy, x, y",
        "py_ccall": "test_input, x, y"
    },
    {
        "code": "G019_COUNTODD",
        "title": "Count Integers with Odd Value",
        "desc": "Given a list of integers, return the count of odd numbers.",
        "func": "solve(number)",
        "ex1_in": "number = [1, 2, 3, 4]", "ex1_out": "2",
        "ex2_in": "number = [2, 4, 6]", "ex2_out": "0",
        "constraints": "<li>Integers can be negative.</li>",
        "py_args": "number",
        "py_sol": "return len([x for x in number if x % 2 != 0])",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G020_LARGEMULT10",
        "title": "Largest Multiple of 10 in List",
        "desc": "Given a list of integers, return the largest number that is a multiple of 10. Return -1 if none found.",
        "func": "solve(number)",
        "ex1_in": "number = [5, 20, 15, 100]", "ex1_out": "100",
        "ex2_in": "number = [1, 2, 3]", "ex2_out": "-1",
        "constraints": "<li>Integers can be negative.</li>",
        "py_args": "number",
        "py_sol": "mults = [x for x in number if x % 10 == 0]\n    return max(mults) if mults else -1",
        "py_gen": "test_input = generate_int_arr()",
        "py_copy": "student_input_copy = list(test_input)",
        "py_scall": "student_input_copy",
        "py_ccall": "test_input"
    },
    {
        "code": "G021_ABSDIFF",
        "title": "Absolute Difference of Two Numbers",
        "desc": "Given two integers a and b, return their absolute difference.",
        "func": "solve(a, b)",
        "ex1_in": "a = 5, b = 10", "ex1_out": "5",
        "ex2_in": "a = 10, b = 5", "ex2_out": "5",
        "constraints": "<li>Inputs are integers.</li>",
        "py_args": "a, b",
        "py_sol": "return abs(a - b)",
        "py_gen": "a = generate_int_num(); b = generate_int_num()",
        "py_copy": "pass",
        "py_scall": "a, b",
        "py_ccall": "a, b"
    }
]

# Create Directory
os.makedirs("generated_files", exist_ok=True)

for p in problems:
    # Generate HTML
    html_content = html_template.format(
        description=p['desc'],
        func_sig=p['func'],
        ex1_in=p['ex1_in'], ex1_out=p['ex1_out'],
        ex2_in=p['ex2_in'], ex2_out=p['ex2_out'],
        constraints=p['constraints']
    )
    with open(f"generated_files/{p['code']}.html", "w") as f:
        f.write(html_content)

    # Generate Python
    py_content = py_template.format(
        extra_generators="",
        args=p['py_args'],
        solution_body=p['py_sol'],
        test_generation=p['py_gen'],
        copy_logic=p['py_copy'],
        student_call_args=p['py_scall'],
        correct_call_args=p['py_ccall']
    )
    with open(f"generated_files/{p['code']}.py", "w") as f:
        f.write(py_content)

# Zip files
with zipfile.ZipFile('coding_problems.zip', 'w') as zipf:
    for root, dirs, files in os.walk("generated_files"):
        for file in files:
            zipf.write(os.path.join(root, file), file)

# Cleanup
import shutil
shutil.rmtree("generated_files")