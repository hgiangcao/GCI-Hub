import random
random.seed(42)

def generate_int_num(min_val=0, max_val=1000):
    return random.randint(min_val, max_val)

def generate_int_arr(n_num=20, min_val=-1000, max_val=1000):
    return [random.randint(min_val, max_val) for _ in range(n_num)]



def correct_solution(number):
    return max(number)

def auto_grade():
    score = 0
    n_test = 100
    status = "Pending"
    
    try:
        for i in range(1, n_test + 1):
            # Generate random test cases
            test_input = generate_int_arr(n_num=4)

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
    print("Compilation Error: Function 'solve' is not defined.")
    print("Status:", "Compile Error")
