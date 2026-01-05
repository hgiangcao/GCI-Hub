# Write your code for G013_COUELE here
def solve(arr):
    # Your code goes here
    avg = sum(arr)/len(arr)
    c =0
    for num in arr:
        if (num > avg):
            c +=1

    return c
