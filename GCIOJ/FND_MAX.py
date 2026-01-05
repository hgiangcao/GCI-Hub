import random

def correct_solution(number):
    return max(number)

def auto_grade():
    print("\n--- Starting Auto-Grader ---")
    score = 0
    total = 5
    
    for i in range(1, total + 1):
        # Generate random test cases
        length = random.randint(5, 20)
        test_input = []  
        for _ in range (length):
            test_input.append(random.randint(-1000, 1000))
        
        try:
            # Create a copy so student code doesn't mess up original data
            student_input_copy = list(test_input)
            
            # Run functions
            student_ans = solve(student_input_copy)
            correct_ans = correct_solution(test_input)
            
            if student_ans == correct_ans:
                score += 1
                print(f"Test Case {i}: PASS") 
            else:
                print(f"Test Case {i}: WRONG ANSWER.")
        
        except Exception as e:
            print(f"Test Case {i}: ERROR. {e}")
            
    print("=" * 30)
    print(f"Final Score: {score} / {total}")

if "solve" in globals():
    auto_grade()
else:
    print("Compilation Error: Function 'solve(number)' is not defined.")