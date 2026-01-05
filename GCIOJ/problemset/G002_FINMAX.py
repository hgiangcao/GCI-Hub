import random
random.seed(42)
def generate_int_num():
    return random.randint(0,1000)

def generate_int_arr(n_num = 20):
    return [random.randint(1, 1000) for _ in range(n_num)]

def generate_int_arr_pos_neg(n_num = 20):
    return [random.randint(-1000, 1000) for _ in range(n_num)]


def generate_int_small_num():
    return random.randint(0,10)


def correct_solution(number):
    return max(number)

def auto_grade():
    
    score = 0
    n_test = 100

    status = "Pending"
    try:
        for i in range(1, n_test + 1):
            # Generate random test cases
            test_input = generate_int_arr_pos_neg()

            try:
                # Create a copy so student code doesn't mess up original data
                student_input_copy = list(test_input)

                # Run functions
                student_ans = solve(student_input_copy)
                correct_ans = correct_solution(test_input)

                if student_ans == correct_ans:
                    score += 1

            except Exception as e:
                status = "Compile Error"
    except Exception as e:
        status = "Compile Error"

    # print("\n--- Starting Auto-Grader ---")
    # print("=" * 30)
    print(f"Final Score: {score} / {n_test}")

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
    print("Compilation Error: Function 'solve(arr)' is not defined.")
    print("Status:", "Compile Error")