# Write your code for G014_PROOF here

def solve(arr):
    ans = 1
    for x in arr:
        if x:
            ans *= x
    return ans
