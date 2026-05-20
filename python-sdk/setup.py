from setuptools import setup, find_packages

with open("README.md", "r", encoding="utf-8") as fh:
    long_description = fh.read()

setup(
    name="pixinlink",
    version="1.0.0",
    author="PixInLink",
    author_email="hello@pixinlink.ru",
    description="AI Image Generation from URL — PixInLink Python SDK",
    long_description=long_description,
    long_description_content_type="text/markdown",
    url="https://github.com/PixInLink/PixInLink",
    packages=find_packages(),
    classifiers=[
        "Development Status :: 5 - Production/Stable",
        "Intended Audience :: Developers",
        "License :: OSI Approved :: MIT License",
        "Programming Language :: Python :: 3",
        "Programming Language :: Python :: 3.8",
        "Programming Language :: Python :: 3.12",
        "Topic :: Internet :: WWW/HTTP",
        "Topic :: Multimedia :: Graphics",
    ],
    python_requires=">=3.8",
    keywords="ai image generation url api pixinlink webp cdn",
)
